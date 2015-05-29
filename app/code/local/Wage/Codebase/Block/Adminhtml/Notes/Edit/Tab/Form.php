<?php
class Wage_Codebase_Block_Adminhtml_Notes_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
     
      $fieldset = $form->addFieldset('notes_form', array('legend'=>Mage::helper('codebase')->__('Note Information')));

      $project = Mage::getModel('codebase/projects')->load($this->getRequest()->getParam('project_id'));


      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('codebase')->__('Title'),
          'required'  => false,
          'name'      => 'title',
      ));
     
      $fieldset->addField('subject', 'textarea', array(
          'label'     => Mage::helper('codebase')->__('Subject'),
          'required'  => false,
          'name'      => 'subject',
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getNotesData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getNotesData());
          Mage::getSingleton('adminhtml/session')->setNotesData(null);
      } elseif ( Mage::registry('notes_data') ) {
          $form->setValues(Mage::registry('notes_data')->getData());
      }
      return parent::_prepareForm();
  }
}
