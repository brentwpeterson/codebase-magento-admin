<?php

class Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Reports extends Mage_Adminhtml_Block_Widget_Grid
{
   public function __construct()
    {
        parent::__construct();
        $this->setId('project_reports_grid');
        $this->setUseAjax(true);
    }

   protected function _prepareCollection()
    {
        $pid = Mage::getSingleton('adminhtml/session')->getProjectId();
	$project = Mage::getModel('codebase/projects')->load($pid);
        $collection = Mage::getModel('codebase/sentreport')->getCollection()
		      ->addFieldToFilter('project_id',$project->getProjectId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('codebase')->__('ID'),
            'width'     => '100',
            'index'     => 'id',
        ));

        $this->addColumn('To Email', array(
            'header'    => Mage::helper('codebase')->__('To Email'),
            'width'     => '100',
            'index'     => 'to_email',
        ));

        $this->addColumn('cc_email', array(
            'header'    => Mage::helper('codebase')->__('CC Email'),
            'width'     => '100',
            'index'     => 'cc_email',
        ));

	$this->addColumn('report_sent_at', array(
            'header'    => Mage::helper('codebase')->__('Sent At'),
            'width'     => '100',
            'index'     => 'report_sent_at',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('codebase/adminhtml_projects/oldreport', array('id' => $row->getId() , 'project_id' => Mage::getSingleton('adminhtml/session')->getProjectId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/reports', array('_current' => true));
    }
}
