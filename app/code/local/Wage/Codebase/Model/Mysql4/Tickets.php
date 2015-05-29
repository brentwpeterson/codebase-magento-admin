<?php
class Wage_Codebase_Model_Mysql4_Tickets extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the timer_id refers to the key field in your database table.
        $this->_init('codebase/tickets', 'id');
    }

    public function getProjects() {

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('codebase/tickets'), array('project_id', 'permalink', 'project_name'))
            ->group('project_id')
            ->order('project_id');

        return $this->_getReadAdapter()->fetchAll($select);
    }
}
