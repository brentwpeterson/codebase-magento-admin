<?php
class Wage_Codebase_Block_Adminhtml_Changepo_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
     
      $fieldset = $form->addFieldset('changepo_form', array('legend'=>Mage::helper('codebase')->__('User Rule Information')));

      $users = Mage::getModel('codebase/users')->getCollection()
          ->addFieldToFilter('enabled',1)
          ->addFieldToFilter('company','Wagento');

      $usersArray = array();
      $usersArray[0] = "-- Select User --";
      foreach($users as $user){
          $usersArray[$user->getUserId()] = $user->getFirstName().' '.$user->getLastName();
      }

      $projects = Mage::getModel('codebase/projects')->getCollection()
          ->addFieldToFilter('status','active')
      ;
      $projectsArray = array();
      $projectsArray[] = array('value' => 'all', 'label' => Mage::helper('codebase')->__('All'));
      foreach ($projects as $project) {
          $projectsArray[] = array('value' => $project->getPermalink(), 'label' => $project->getProjectName());
      }

      $fieldset->addField('rule_title', 'text', array(
          'label'     => Mage::helper('codebase')->__('Rule Title'),
          'required'  => true,
          'name'      => 'rule_title'
      ));

      $fieldset->addField('current_user_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('Current User'),
          'required'  => true,
          'name'      => 'current_user_id',
          'values' => $usersArray,
      ));

      $fieldset->addField('new_user_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('New User'),
          'required'  => true,
          'name'      => 'new_user_id',
          'values' => $usersArray,
      ));

      $fieldset->addField('effective_from', 'date', array(
          'label'     => Mage::helper('codebase')->__('Start Date'),
          'required'  => true,
          'name'      => 'effective_from',
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
          'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
      ));

      $fieldset->addField('effective_to', 'date', array(
          'label'     => Mage::helper('codebase')->__('End Date'),
          'required'  => true,
          'name'      => 'effective_to',
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
          'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
      ));

      $fieldset->addField('projects', 'multiselect', array(
          'label'     => Mage::helper('codebase')->__('Projects'),
          'required'  => true,
          'name'      => 'projects',
          'values' => $projectsArray,
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('codebase')->__('Status'),
          'name'      => 'status',
          'options' => array(
              1 => 'Enabled',
              0 => 'Disabled'
          ),
          'required' => true
      ));
      
      if (Mage::getSingleton('adminhtml/session')->getChangepoData()) {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getChangepoData());
          Mage::getSingleton('adminhtml/session')->setChangepoData(null);
      } elseif (Mage::registry('changepo_data')) {
          $form->setValues(Mage::registry('changepo_data')->getData());
      }
      return parent::_prepareForm();
  }
}
