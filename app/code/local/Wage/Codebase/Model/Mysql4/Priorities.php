<?php
class Wage_Codebase_Model_Mysql4_Priorities extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {    
        $this->_init('codebase/priorities', 'id');
    }
}
