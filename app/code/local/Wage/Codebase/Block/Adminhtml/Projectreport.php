<?php

class Wage_Codebase_Block_Adminhtml_Projectreport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'codebase';
        $this->_controller = 'adminhtml_projectreport';
        $this->_headerText = Mage::helper('codebase')->__('Project Report');

        parent::__construct();
        $this->_removeButton('add');
    }
}