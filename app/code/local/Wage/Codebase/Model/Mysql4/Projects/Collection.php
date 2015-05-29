<?php
class Wage_Codebase_Model_Mysql4_Projects_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codebase/projects');
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        if(count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT ".implode(", ", $group).")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

    protected function _joinFields($from = '', $to = '')
    {
        $from = Mage::getModel('core/date')->timestamp(strtotime($from));
        $from = date('Y-m-d', $from);

        $to = Mage::getModel('core/date')->timestamp(strtotime($to));
        $to = date('Y-m-d', $to);

        $productOwner = Mage::getSingleton('core/session')->getReportProdcutOwner();

        if ($productOwner) {
            $this->getSelect()->reset()
                ->from(
                    array('timetracking' => $this->getTable('codebase/timetracking')),
                    array(
                        'time_spent' => 'SUM(timetracking.minutes)'
                    ))
                ->joinLeft(
                    array('projects' => $this->getTable('codebase/projects')),
                    'timetracking.project_id = projects.project_id',
                    array('project_name','project_id', 'user_id')
                )
                ->where("projects.status = 'active' AND projects.user_id =".$productOwner)
                ->where("timetracking.session_date >= '".$from."' AND timetracking.session_date <='".$to."'")
                ->group('timetracking.project_id')
            ;
        } else {
            $this->getSelect()->reset()
                ->from(
                    array('timetracking' => $this->getTable('codebase/timetracking')),
                    array(
                        'time_spent' => 'SUM(timetracking.minutes)'
                    ))
                ->joinLeft(
                    array('projects' => $this->getTable('codebase/projects')),
                    'timetracking.project_id = projects.project_id',
                    array('project_name','project_id', 'user_id')
                )
                ->where("projects.status = 'active'")
                ->where("timetracking.session_date >= '".$from."' AND timetracking.session_date <='".$to."'")
                ->group('timetracking.project_id')
            ;
        }


        $this->getSelect()->order('time_spent DESC');
        return $this;
    }

    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->_joinFields($from, $to);
        return $this;
    }

    public function setStoreIds($storeIds)
    {
        return $this;
    }
}
