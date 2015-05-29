<?php
class Wage_Codebase_Block_Adminhtml_Ticketsreport extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_ticketsreport';
    $this->_blockGroup = 'codebase';  
    $this->_headerText = Mage::helper('codebase')->__('Updated estimated time for following Tickets');
    parent::__construct();
    $this->_removeButton('add');
   
  }

	
}
