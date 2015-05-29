<?php
class Wage_Codebase_Block_Adminhtml_Reports_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('reportsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $excludeTypes = explode(',',Mage::getStoreConfig('codebase/report/exclude_category'));
        if(count($excludeTypes) == 1){
            $excludeTypes = array($excludeTypes);
        }

        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('estimated_time',0)
            ->addFieldToFilter('category_name', array('nin' => $excludeTypes))
            ->addFieldToFilter('status_name', array('nin' => array('Need Time Estimate')))
            ->setOrder('project_name','ASC');

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));

        $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'))
            ->where('company = "Wagento" ');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('project_name', array(
            'header' => Mage::helper('codebase')->__('Project'),
            'index' => 'project_name',

        ));
//        $this->addColumn('ticket_id', array(
//            'header'    =>Mage::helper('codebase')->__('Ticket Id'),
//            'index'     =>'ticket_id',
//            'type'      =>'number'
//        ));
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
        $this->addColumn('priority_name', array(
            'header' => Mage::helper('codebase')->__('Priority'),
            'index' => 'priority_name',

        ));
        $this->addColumn('status_name', array(
            'header' => Mage::helper('codebase')->__('Status'),
            'index' => 'status_name',

        ));
        $this->addColumn('category_name', array(
            'header' => Mage::helper('codebase')->__('Category'),
            'index' => 'category_name',

        ));
        
        $this->addColumn('Codebase', array(
            'header' => Mage::helper('codebase')->__('Ticket Number'),
            'align' => 'left',
            'index' => 'ticket_id',
            'width'     => '70',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Reports_Grid_Renderer_Link',
            'permalink' => 'permalink'
        ));
        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('customer')->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
            'width'     => '70',
        ));
        $this->addColumn('estimate_need', array(
            'header'    =>Mage::helper('codebase')->__('Estimate Need'),
            'index'     =>'estimate_need',
            'type'      => 'options',
            'options'   => array(
                'Wagento' => 'Yes',
                0 => 'No',
            ),
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
                ->addFieldToFilter('estimated_time',$value);
        }

        return $this;
    }

    /**
     * add massAction column
     * @author atheotsky
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $estimateNeed = array(
                1 => 'Yes',
                0 => 'No',
            );

        $this->getMassactionBlock()->addItem('estimate_need', array(
            'label'=> Mage::helper('codebase')->__('Change Estimate Need'),
            'url'  => $this->getUrl('*/*/estimateNeed', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'estimate_need',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('catalog')->__('Select Option'),
                    'values' => $estimateNeed
                )
            )
        ));

        $this->getMassactionBlock()->addItem('need_time_estimate', array(
            'label'=> Mage::helper('codebase')->__('Need time estimate'),
            'url'  => $this->getUrl('*/*/needTimeEstimate'),
        ));

        return $this;
    }
    
}
