<?php
class Wage_Codebase_Model_Mysql4_Activities_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codebase/activities');
    }
    public function getSelectCountSql() {
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

        echo "Following report is from ".$from.' to '.$to;

        $project = Mage::getSingleton('core/session')->getProject();

        $roles = Mage::getModel('admin/roles')->getCollection();
                $roles->addFieldToFilter('role_name', 'Product Owner');
	 	        foreach($roles as $role){
         	            $adminroles = Mage::getModel('admin/roles')->load($role->getRoleId())->getRoleUsers();
	 	            foreach($adminroles as $adminuserid){
             	                $adminuserModel = Mage::getModel('admin/user')->load($adminuserid);
	 	                $productOwner[] = $adminuserModel->getApiUser();
	 	            }
	 	        }
        $productOwnerCollection = Mage::getModel("codebase/users")
	 	          ->getCollection()
	 	          ->addFieldToFilter('user_name', array('in' => $productOwner));

        foreach($productOwnerCollection as $productOwnerValues){
	 	            $productOwnerArray[] = $productOwnerValues->getEmailAddress();
	        }

        if ($project) {
            $this->addFieldToFilter('project_id',$project)
                ->addFieldToFilter('actor_email', array('in' => $productOwnerArray))
                ->addFieldToFilter('timestamp', array(
                    'from'     => $from,
                    'to'       => $to,
                ));

        } else {
            $this->addFieldToFilter('actor_email', array('in' => $productOwnerArray))
                ->addFieldToFilter('timestamp', array(
                    'from'     => $from,
                    'to'       => $to,
                ))
                ->addFieldToFilter('content', array('neq' => NULL));

        }

        $this->getSelect()
        ->columns('COUNT(*) AS ownerupdate, SEC_TO_TIME(SUM(TIME_TO_SEC(time_added))) AS hourslogged')
        ->order(new Zend_Db_Expr("`ownerupdate` DESC"))
        ->group(array('project_id','actor_email'));


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
