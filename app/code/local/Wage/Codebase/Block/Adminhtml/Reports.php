<?php
class Wage_Codebase_Block_Adminhtml_Reports extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_reports';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('List of Tickets without estimation');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
