<?php
class Wage_Codebase_Model_Sentreport extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codebase/sentreport');
    }
}