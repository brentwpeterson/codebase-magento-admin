<?php

class Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);

      $productCollection = Mage::getResourceModel('catalog/product_collection')
          ->addAttributeToSelect('name')
          ->addAttributeToSelect('id')
          ->addAttributeToFilter('type_id','virtual');
      $productArray = array();
      $productArray[0] = "-- Please select product --";
      foreach($productCollection as $project){
          $productArray[$project->getId()] = $project->getName();
      }
      ksort($productArray);

      $projectData = Mage::registry('projects_data')->getData();
      $projectId = $projectData['project_id'];
      $projectDevelopers = Mage::getModel("codebase/projectindex")->getCollection()
          ->addFieldToFilter('project_id', $projectId);
      $projectDevelopers->getSelect()->join(array('t1' => Mage::getConfig()->getTablePrefix().'codebase_users'), 'main_table.user_id = t1.user_id')
          ->where("t1.enabled = '1'");

      $backlogArray = array();
      $backlogArray[0] = Mage::helper('codebase')->__('-- Select Backlog --');

      $projectUsers = array();
      $projectUsers[0] = Mage::helper('codebase')->__('-- Select Product Owner --');
      $projectOwners = Mage::helper('codebase')->getProductOwners();

      $projectUsers = array_replace($projectUsers, $projectOwners);

      $clientsArray = array();
      $clientsArray[0] = Mage::helper('codebase')->__('-- Select Client Name --');

      foreach($projectDevelopers as $projectDeveloper){
          $userName = $projectDeveloper->getFirstName().' '.$projectDeveloper->getLastName();
          if ($projectDeveloper->getCompany() != 'Wagento') {
              $clientsArray[$projectDeveloper->getUserId()] = $userName;
          }
          else{
            $techleadUsers[$projectDeveloper->getUserId()] = $userName;
          }

          $userNameString = $userName;
          if(strpos($userNameString, 'klog')){
              $backlogArray[$projectDeveloper->getUserId()] = $userName;
          }
      }

      $techleadArray[0] = Mage::helper('codebase')->__('-- Select Tech Lead --');
      $techleadArray = array_replace($techleadArray, $techleadUsers); 

      $productStatusArray = array();
      $productStatusArray['active'] = 'Active';
      $productStatusArray['archived'] = 'Archived';
      $productStatusArray['on_hold'] = 'On Hold';

      $teamCollection = Mage::getModel('codebase/teams')->getCollection();
      $teamArray = array();
      $teamArray[0] = "-- Select Team --";
      foreach($teamCollection as $team){
          $teamArray[$team->getId()] = $team->getTeamName();
      }

      $fieldset = $form->addFieldset('projects_form', array('legend'=>Mage::helper('codebase')->__('Project Information')));

      $fieldset->addField('project_id', 'text', array(
          'label'     => Mage::helper('codebase')->__('Project Id'),
          'required'  => false,
          'name'      => 'project_id',
          'readonly'  => true,
          'disabled'  => true
      ));

      $fieldset->addField('project_name', 'text', array(
          'label'     => Mage::helper('codebase')->__('Project Name'),
          'required'  => false,
          'name'      => 'project_name',
          'readonly'  => true

      ));

      /*$fieldset->addField('owner_name', 'text', array(
          'label'     => Mage::helper('codebase')->__('Owner Name'),
          'required'  => false,
          'name'      => 'owner_name',

      ));

      $fieldset->addField('owner_email', 'text', array(
          'label'     => Mage::helper('codebase')->__('Owner Email'),
          'required'  => false,
          'name'      => 'owner_email',

      ));

      $fieldset->addField('owner_phone', 'text', array(
          'label'     => Mage::helper('codebase')->__('Owner Phone'),
          'required'  => false,
          'name'      => 'owner_phone',
      ));
       */
      $fieldset->addField('product_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('Product'),
          'required'  => false,
          'name'      => 'product_id',
          'values' => $productArray,
      ));

      $fieldset->addField('client_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('Client'),
          'required'  => false,
          'name'      => 'client_id',
          'values' => $clientsArray,
      ));

      $fieldset->addField('user_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('Product Owner'),
          'required'  => false,
          'name'      => 'user_id',
          'values' => $projectUsers,
      ));

      $fieldset->addField('techlead_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('Techlead'),
          'required'  => false,
          'name'      => 'techlead_id',
          'values' => $techleadArray,
      ));

      $fieldset->addField('team_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('Team'),
          'required'  => false,
          'name'      => 'team_id',
          'values' => $teamArray,
      ));

      $fieldset->addField('backlog_id', 'select', array(
          'label'     => Mage::helper('codebase')->__('Backlog'),
          'required'  => false,
          'name'      => 'backlog_id',
          'values' => $backlogArray,
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('codebase')->__('Status'),
          'required'  => false,
          'name'      => 'status',
          'values' => $productStatusArray,
      ));
      $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
      $fieldset->addField('last_client_contact', 'date', array(
          'name'   => 'last_client_contact',
          'label'  => Mage::helper('codebase')->__('Last time client contact at'),
          'image'  => $this->getSkinUrl('images/grid-cal.gif'),
          'input_format' => $dateFormatIso,
          'format'       => $dateFormatIso,
          'time' => true,
          'readonly'  => true,
          'disabled'  => true,
          'note' => Mage::helper('codebase')->__('This field will show you when last time client contact by product owner. Product owner has to update this field.')
      ));
      if ( Mage::getSingleton('adminhtml/session')->getProjectsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getProjectsData());
          Mage::getSingleton('adminhtml/session')->setProjectsData(null);
      } elseif ( Mage::registry('projects_data') ) {
          $form->setValues(Mage::registry('projects_data')->getData());
      }
      return parent::_prepareForm();
  }
}
