<?php
class Wage_Codebase_Block_Adminhtml_Prioritytickets extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_prioritytickets';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Crictical and High Priority Tickets');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
