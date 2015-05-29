<?php
class Wage_Codebase_Block_Adminhtml_Productownertickets extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_productownertickets';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Product Owner Non Updated Ticket Report');
    parent::__construct();
    $this->_removeButton('add');
      
  }

	
}
