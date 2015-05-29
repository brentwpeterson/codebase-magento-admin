<?php
class Wage_Codebase_Block_Adminhtml_Billingreport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_billingreport';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Billable Reports');
      $this->_addButtonLabel = Mage::helper('codebase')->__('Add Employee');
      parent::__construct();
      $this->_removeButton('add');
      $this->_addButton('adminhtml_tickets', array(
          'label' => $this->__('Create Report'),
          'onclick' => "setLocation('{$this->getUrl('*/*/generatereport')}')",
      ));

  }
}
