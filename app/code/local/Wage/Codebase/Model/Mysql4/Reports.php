<?php
class Wage_Codebase_Model_Mysql4_Reports extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {    
        $this->_init('codebase/reports', 'id');
    }
}
