<?php
class Wage_Codebase_Model_Mysql4_Billing extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {    
        $this->_init('codebase/billing', 'id');
    }
}
