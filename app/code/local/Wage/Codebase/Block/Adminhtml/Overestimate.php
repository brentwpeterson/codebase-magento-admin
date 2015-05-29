<?php
class Wage_Codebase_Block_Adminhtml_Overestimate extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_overestimate';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Over estimate tickets');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
