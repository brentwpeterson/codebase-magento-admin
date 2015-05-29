<?php

class Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Notes extends Mage_Adminhtml_Block_Widget_Grid
{
   public function __construct()
    {
        parent::__construct();
        $this->setId('project_notes_grid');
        $this->setUseAjax(true);
    }

   protected function _prepareCollection()
    {
        $pid = Mage::getSingleton('adminhtml/session')->getProjectId();
	$project = Mage::getModel('codebase/projects')->load($pid);
        $collection = Mage::getModel('codebase/notes')->getCollection()
		      ->addFieldToFilter('project_id',$project->getProjectId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

public function getMainButtonsHtml()
{
    $html = parent::getMainButtonsHtml();//get the parent class buttons
    $addButton = $this->getLayout()->createBlock('adminhtml/widget_button') //create the add button
        ->setData(array(
            'label'     => Mage::helper('adminhtml')->__('Add Note'),
            'onclick'   => "setLocation('".$this->getUrl('codebase/adminhtml_notes/new')."')",
            'class'   => 'task'
        ))->toHtml();
    return $addButton.$html;
}

protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'    => Mage::helper('codebase')->__('Title'),
            'width'     => '100',
            'index'     => 'title',
        ));

        $this->addColumn('subject', array(
            'header'    => Mage::helper('codebase')->__('Subject'),
            'width'     => '100',
            'index'     => 'subject',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('codebase')->__('Created At'),
            'width'     => '100',
            'index'     => 'created_at',
        ));

	$this->addColumn('updated_at', array(
            'header'    => Mage::helper('codebase')->__('Updated At'),
            'width'     => '100',
            'index'     => 'updated_at',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('codebase/adminhtml_notes/edit', array('id' => $row->getId() , 'project_id' => Mage::getSingleton('adminhtml/session')->getProjectId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/notes', array('_current' => true));
    }
}
