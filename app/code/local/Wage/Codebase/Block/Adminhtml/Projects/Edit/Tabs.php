<?php

class Wage_Codebase_Block_Adminhtml_Projects_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('projects_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('codebase')->__('Project Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('codebase')->__('Project Information'),
          'title'     => Mage::helper('codebase')->__('Project Information'),
          'content'   => $this->getLayout()->createBlock('codebase/adminhtml_projects_edit_tab_form')->toHtml(),
      ));

      $this->addTab('orders', array(
          'label'     => Mage::helper('codebase')->__('Notes'),
          'class'     => 'ajax',
          'url'       => $this->getUrl('*/*/notes', array('_current' => true)),
      ));
	
      $this->addTab('reports', array(
          'label'     => Mage::helper('codebase')->__('Report Archieve'),
          'class'     => 'ajax',
          'url'       => $this->getUrl('*/*/reports', array('_current' => true)),
      ));

      return parent::_beforeToHtml();
  }
}
