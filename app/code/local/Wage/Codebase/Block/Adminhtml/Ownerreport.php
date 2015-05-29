<?php

class Wage_Codebase_Block_Adminhtml_Ownerreport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'codebase';
        $this->_controller = 'adminhtml_ownerreport';
        $this->_headerText = Mage::helper('codebase')->__('Product Owner Report');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('codebase/time');
        $time = $readConnection->fetchCol('SELECT last_activity_time FROM ' . $table);
        if($time[0]){
            $this->_headerText = Mage::helper('codebase')->__('Product Owner Report - All Activities (Last refreshed on %s)',$time[0]);
        } else {
            //$this->_headerText = Mage::helper('codebase')->__('Tickets');
        }
        parent::__construct();
        $this->_removeButton('add');
    }
}
