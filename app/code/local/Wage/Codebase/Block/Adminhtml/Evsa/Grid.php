<?php
class Wage_Codebase_Block_Adminhtml_Evsa_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('evsaGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('codebase/tickets')->getCollection()            
            ->addFieldToFilter('total_time_spent', array('neq' => 'NULL' ))
            ->addFieldToFilter('orig_estimated_time', array('neq' => 'NULL' ))
            ->setOrder('updated_at','DESC');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $project_array = array();
        $projects = Mage::getModel('codebase/projects')->getCollection()->addFieldToFilter('status','active');
        foreach($projects as $project){
            $project_array[$project['project_name']] = $project['project_name'];
        }
        ksort($project_array);

        $this->addColumn('project_name', array(
            'header' => Mage::helper('codebase')->__('Project'),
            'index' => 'project_name',
            'type'      => 'options',
            'options' => $project_array,
        ));

        $this->addColumn('ticket_id', array(
            'header'    =>Mage::helper('codebase')->__('Ticket Id'),
            'index'     =>'ticket_id',
            'type'      =>'number',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Evsa_Renderer_Link',
        ));

        $this->addColumn('summary', array(
            'header' => Mage::helper('codebase')->__('Summary'),
            'index' => 'summary',
        ));

        $this->addColumn('orig_estimator', array(
            'header'    =>Mage::helper('codebase')->__('Origial Estimator'),
            'index'     =>'orig_estimator',
        ));

        $this->addColumn('orig_estimated_time', array(
            'header' => Mage::helper('codebase')->__('Original Estimate'),
            'type'      =>'number',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Evsa_Renderer_Hour',
            'index' => 'orig_estimated_time',
        ));       

        $this->addColumn('final_estimate', array(
            'header' => Mage::helper('codebase')->__('Final Estimate'),
            'type'      =>'number',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Evsa_Renderer_Hour',
            'index' => 'final_estimate',
        ));       

        $this->addColumn('total_time_spent', array(
            'header' => Mage::helper('codebase')->__('Hours Worked'),
            'type'      =>'number',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Evsa_Renderer_Hour',
            'index' => 'total_time_spent',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('codebase')->__('Date Started'),
            'index' => 'created_at',
            'type' => 'date',

        ));

        $this->addColumn('updated_at', array(
            'header' => Mage::helper('codebase')->__('Date Computed'),
            'index' => 'updated_at',
            'type' => 'date',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _filterConditionCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == NULL) {
            return $this;
        }
        else {
            $this->getCollection()
                ->addFieldToFilter('orig_estimated_time',$value);
        }

        return $this;
    }


}
