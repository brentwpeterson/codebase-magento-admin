<?php

class Wage_Codebase_Block_Adminhtml_Permissions_User_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('permissions_user');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('user_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('Account Information')));

        if ($model->getUserId()) {
            $fieldset->addField('user_id', 'hidden', array(
                'name' => 'user_id',
            ));
        } else {
            if (! $model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $fieldset->addField('username', 'text', array(
            'name'  => 'username',
            'label' => Mage::helper('adminhtml')->__('User Name'),
            'id'    => 'username',
            'title' => Mage::helper('adminhtml')->__('User Name'),
            'required' => true,
        ));

        $fieldset->addField('firstname', 'text', array(
            'name'  => 'firstname',
            'label' => Mage::helper('adminhtml')->__('First Name'),
            'id'    => 'firstname',
            'title' => Mage::helper('adminhtml')->__('First Name'),
            'required' => true,
        ));

        $fieldset->addField('lastname', 'text', array(
            'name'  => 'lastname',
            'label' => Mage::helper('adminhtml')->__('Last Name'),
            'id'    => 'lastname',
            'title' => Mage::helper('adminhtml')->__('Last Name'),
            'required' => true,
        ));

        $fieldset->addField('email', 'text', array(
            'name'  => 'email',
            'label' => Mage::helper('adminhtml')->__('Email'),
            'id'    => 'customer_email',
            'title' => Mage::helper('adminhtml')->__('User Email'),
            'class' => 'required-entry validate-email',
            'required' => true,
        ));
        $fieldset->addField('current_password', 'obscure', array(
            'name'  => 'current_password',
            'label' => Mage::helper('adminhtml')->__('Current Admin Password'),
            'id'    => 'current_password',
            'title' => Mage::helper('adminhtml')->__('Current Admin Password'),
            'class' => 'input-text',
            'required' => true,
        ));
        if ($model->getUserId()) {
            $fieldset->addField('password', 'password', array(
                'name'  => 'new_password',
                'label' => Mage::helper('adminhtml')->__('New Password'),
                'id'    => 'new_pass',
                'title' => Mage::helper('adminhtml')->__('New Password'),
                'class' => 'input-text validate-admin-password',
            ));

            $fieldset->addField('confirmation', 'password', array(
                'name'  => 'password_confirmation',
                'label' => Mage::helper('adminhtml')->__('Password Confirmation'),
                'id'    => 'confirmation',
                'class' => 'input-text validate-cpassword',
            ));
        }
        else {
           $fieldset->addField('password', 'password', array(
                'name'  => 'password',
                'label' => Mage::helper('adminhtml')->__('Password'),
                'id'    => 'customer_pass',
                'title' => Mage::helper('adminhtml')->__('Password'),
                'class' => 'input-text required-entry validate-admin-password',
                'required' => true,
            ));
           $fieldset->addField('confirmation', 'password', array(
                'name'  => 'password_confirmation',
                'label' => Mage::helper('adminhtml')->__('Password Confirmation'),
                'id'    => 'confirmation',
                'title' => Mage::helper('adminhtml')->__('Password Confirmation'),
                'class' => 'input-text required-entry validate-cpassword',
                'required' => true,
            ));
        }

        if (Mage::getSingleton('admin/session')->getUser()->getId() != $model->getUserId()) {
            $fieldset->addField('is_active', 'select', array(
                'name'  	=> 'is_active',
                'label' 	=> Mage::helper('adminhtml')->__('This account is'),
                'id'    	=> 'is_active',
                'title' 	=> Mage::helper('adminhtml')->__('Account Status'),
                'class' 	=> 'input-select',
                'style'		=> 'width: 80px',
                'options'	=> array('1' => Mage::helper('adminhtml')->__('Active'), '0' => Mage::helper('adminhtml')->__('Inactive')),
            ));
        }

        $fieldset->addField('api_user', 'text', array(
            'name'  => 'api_user',
            'label' => Mage::helper('adminhtml')->__('API User'),
            'id'    => 'api_user',
            'title' => Mage::helper('adminhtml')->__('API User'),
            'required' => false,
        ));

        $fieldset->addField('api_key', 'text', array(
            'name'  => 'api_key',
            'label' => Mage::helper('adminhtml')->__('API Key'),
            'id'    => 'api_key',
            'title' => Mage::helper('adminhtml')->__('API Key'),
            'required' => false,
        ));

        $fieldset->addField('wagento_staff', 'select', array(
            'name'  => 'wagento_staff',
            'label' => Mage::helper('adminhtml')->__('Wagento Staff?'),
            'id'    => 'wagento_staff',
            'title' => Mage::helper('adminhtml')->__('Wagento Staff?'),
            'required' => false,
            'value'  => '1',
            'values' => array('0' => 'No','1' => 'Yes')
        ));

        $fieldset->addField('user_roles', 'hidden', array(
            'name' => 'user_roles',
            'id'   => '_user_roles',
        ));

        $data = $model->getData();

        unset($data['password']);

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
