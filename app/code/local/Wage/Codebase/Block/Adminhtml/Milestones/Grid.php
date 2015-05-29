<?php
class Wage_Codebase_Block_Adminhtml_Milestones_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('milestonesGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('codebase/milestones')->getCollection()
            ->addFieldToFilter('status','active')
            ->setOrder('project_name','ASC');

  
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('project_name', array(
            'header' => Mage::helper('codebase')->__('Project'),
            'index' => 'project_name',

        ));

	 $this->addColumn('name', array(
            'header' => Mage::helper('codebase')->__('Milestone'),
            'index' => 'name',

        ));

	$this->addColumn('description', array(
            'header' => Mage::helper('codebase')->__('Description'),
            'index' => 'description',

        ));
	$this->addColumn('estimated_time', array(
            'header' => Mage::helper('codebase')->__('Estimated Time'),
            'index' => 'estimated_time',

        ));
        $this->addColumn('total_time_spent', array(
            'header' => Mage::helper('codebase')->__('Spent(Mins.)'),  
	    'index' => 'milestone_id',          
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Milestones_Renderer_Timespent',
            'milestone_id' => 'milestone_id',
            'project_id' => 'project_id',
        ));

      $this->addColumn('Codebase', array(
            'header' => Mage::helper('codebase')->__('Left(Mins.)'),
            'align' => 'left',
            'index' => 'estimated_time',
            'width'     => '70',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Milestones_Renderer_Timeleft',
            'milestone_id' => 'milestone_id',
            'project_id' => 'project_id',
        ));
	/*
	$this->addColumn('start_at', array(
            'header'    => Mage::helper('codebase')->__('Start At'),
            'index'     => 'start_at',
            'type'      => 'date',
            'width'     => '70',
        ));
	
	$this->addColumn('deadline', array(
            'header'    => Mage::helper('codebase')->__('Deadline'),
            'index'     => 'deadline',
            'type'      => 'date',
            'width'     => '70',
        ));
	*/
       
        $this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    
    
}
