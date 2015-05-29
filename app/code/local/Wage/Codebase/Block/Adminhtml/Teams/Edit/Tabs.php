<?php

class Wage_Codebase_Block_Adminhtml_Teams_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('teams_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('codebase')->__('Team Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('codebase')->__('Team Information'),
          'title'     => Mage::helper('codebase')->__('Team Information'),
          'content'   => $this->getLayout()->createBlock('codebase/adminhtml_teams_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
