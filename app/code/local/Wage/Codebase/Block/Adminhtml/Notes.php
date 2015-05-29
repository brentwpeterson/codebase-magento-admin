<?php
class Wage_Codebase_Block_Adminhtml_Notes extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_notes';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Notes Manager');
    $this->_addButtonLabel = Mage::helper('codebase')->__('Add Note');
    parent::__construct();


  }
}
