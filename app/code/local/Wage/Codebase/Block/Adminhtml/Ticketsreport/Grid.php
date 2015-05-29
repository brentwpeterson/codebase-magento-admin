<?php
class Wage_Codebase_Block_Adminhtml_Ticketsreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ticketsreportGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('codebase/ticketsreport')->getCollection()            
            ->setOrder('updated_at','DESC');
      
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('project_name', array(
            'header' => Mage::helper('codebase')->__('Project'),
            'index' => 'project_name',

        ));
        $this->addColumn('ticket_id', array(
            'header'    =>Mage::helper('codebase')->__('Ticket Id'),
            'index'     =>'ticket_id',
            'type'      =>'number'
        ));
        $this->addColumn('assignee', array(
            'header'    =>Mage::helper('codebase')->__('Assignee'),
            'index'     =>'assignee',
        ));
        
        $this->addColumn('summary', array(
            'header' => Mage::helper('codebase')->__('Summary'),
            'index' => 'summary',

        ));
        $this->addColumn('ticket_type', array(
            'header' => Mage::helper('codebase')->__('Type'),
            'index' => 'ticket_type',

        ));       
        
        $this->addColumn('orig_estimated_time', array(
            'header' => Mage::helper('codebase')->__('Orig. Est. Time'),
            'index' => 'orig_estimated_time',
            'filter_condition_callback' => array($this, '_filterConditionCallback')
        ));
        
        $this->addColumn('updated_estimated_time', array(
            'header' => Mage::helper('codebase')->__('Updated Est. Time'),
            'index' => 'updated_estimated_time',

        ));
        $this->addColumn('Codebase', array(
            'header' => Mage::helper('codebase')->__('Codebase Link'),
            'align' => 'left',
            'index' => 'ticket_id',
            'width'     => '70',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Ticketsreport_Grid_Renderer_Link',
            'permalink' => 'permalink'
        ));
        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('customer')->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
            'width'     => '70',
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
