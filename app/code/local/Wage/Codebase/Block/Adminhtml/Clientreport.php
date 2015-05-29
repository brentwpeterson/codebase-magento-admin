<?php
class Wage_Codebase_Block_Adminhtml_Clientreport extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_clientreport';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Tickets last updated by client and does not changed user');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
