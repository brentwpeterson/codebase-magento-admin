<?php

class Wage_Codebase_Block_Adminhtml_Teams_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'codebase';
        $this->_controller = 'adminhtml_teams';
        
        $this->_updateButton('save', 'label', Mage::helper('codebase')->__('Save Team'));
        //$this->_updateButton('delete', 'label', Mage::helper('codebase')->__('Delete Project'));
        $this->_removeButton('delete');

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('projects_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'projects_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'projects_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('teams_data') && Mage::registry('teams_data')->getId() ) {
            return Mage::helper('codebase')->__("Edit Team '%s'", $this->htmlEscape(Mage::registry('teams_data')->getTeamName()));
        } else {
            return Mage::helper('codebase')->__('Add Team');
        }
    }
}
