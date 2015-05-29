<?php
class Wage_Codebase_Block_Adminhtml_Projects extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_projects';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Projects Manager');
    $this->_addButtonLabel = Mage::helper('codebase')->__('Add Projects');
    parent::__construct();
      $this->_removeButton('add');

  }
}
