<?php
class Wage_Codebase_Model_Mysql4_Timetracking extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('codebase/timetracking', 'id');
    }
}
