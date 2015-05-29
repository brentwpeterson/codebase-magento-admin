<?php
class Wage_Codebase_Block_Adminhtml_Changepo extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_changepo';
        $this->_blockGroup = 'codebase';
        $this->_headerText = Mage::helper('codebase')->__('Change User Rule');
        parent::__construct();
        //$this->_removeButton('add');
    }
}
