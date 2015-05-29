<?php
class Wage_Codebase_Block_Adminhtml_Billing extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_billing';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Generate report by user');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
