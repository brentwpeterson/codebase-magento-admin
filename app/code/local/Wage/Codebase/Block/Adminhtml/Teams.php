<?php
class Wage_Codebase_Block_Adminhtml_Teams extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_teams';
    $this->_blockGroup = 'codebase';
    $this->_headerText = Mage::helper('codebase')->__('Tickets Manager');
    $this->_addButtonLabel = Mage::helper('codebase')->__('Add Team');
    parent::__construct();

  }
}
