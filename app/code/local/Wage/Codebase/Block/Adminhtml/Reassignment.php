<?php
class Wage_Codebase_Block_Adminhtml_Reassignment extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_reassignment';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Ticket Reassignment Report');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
