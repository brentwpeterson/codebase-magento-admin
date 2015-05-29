<?php

class Wage_Codebase_Block_Adminhtml_Changepo_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'codebase';
        $this->_controller = 'adminhtml_changepo';
        
        $this->_updateButton('save', 'label', Mage::helper('codebase')->__('Save User Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('codebase')->__('Delete User Rule'));
        //$this->_removeButton('delete');

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('changepo_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'changepo_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'changepo_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('changepo_data') && Mage::registry('changepo_data')->getId() ) {
            return Mage::helper('codebase')->__("Edit User Rule '%s'", $this->htmlEscape(Mage::registry('changepo_data')->getRuleTitle()));
        } else {
            return Mage::helper('codebase')->__('Add User Rule');
        }
    }
}
