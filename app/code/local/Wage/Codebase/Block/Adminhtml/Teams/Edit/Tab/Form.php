<?php

class Wage_Codebase_Block_Adminhtml_Teams_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);

      $users = Mage::getModel('codebase/users')->getCollection()
                        ->addFieldToFilter('enabled',1)
                        ->addFieldToFilter('company','Wagento');

      $usersArray = array();
      foreach($users as $user){
          $usersArrayItem['label'] = $user->getFirstName().' '.$user->getLastName();
          $usersArrayItem['value'] = $user->getUserId();
          $usersArray[] = $usersArrayItem;
      }

      $fieldset = $form->addFieldset('teams_form', array('legend'=>Mage::helper('codebase')->__('Team Information')));
     
      $fieldset->addField('team_name', 'text', array(
          'label'     => Mage::helper('codebase')->__('Team Name'),
          'required'  => true,
          'name'      => 'team_name',
      ));
      $fieldset->addField('members', 'multiselect', array(
          'label'     => Mage::helper('codebase')->__('Team Members'),
          'required'  => false,
          'name'      => 'members',
          'values' => $usersArray,
      ));

      
      if ( Mage::getSingleton('adminhtml/session')->getTeamsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getTeamsData());
          Mage::getSingleton('adminhtml/session')->setTeamsData(null);
      } elseif ( Mage::registry('teams_data') ) {
          $form->setValues(Mage::registry('teams_data')->getData());
      }
      return parent::_prepareForm();
  }
}
