<?php
class Wage_Codebase_Block_Adminhtml_Development extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_development';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Development');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
