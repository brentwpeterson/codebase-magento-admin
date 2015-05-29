<?php
class Wage_Codebase_Block_Adminhtml_Notes_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('notes_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('codebase')->__('Notes Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('codebase')->__('Notes Information'),
          'title'     => Mage::helper('codebase')->__('Notes Information'),
          'content'   => $this->getLayout()->createBlock('codebase/adminhtml_notes_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
