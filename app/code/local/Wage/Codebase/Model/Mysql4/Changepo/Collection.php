<?php
class Wage_Codebase_Model_Mysql4_Changepo_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codebase/changepo');
    }
}
