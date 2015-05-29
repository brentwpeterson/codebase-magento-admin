<?php
class Wage_Codebase_Model_Mysql4_Projectindex extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the timer_id refers to the key field in your database table.
        $this->_init('codebase/projectindex', 'entity_id');
    }
}
