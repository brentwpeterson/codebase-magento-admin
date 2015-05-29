<?php
class Wage_Codebase_Block_Adminhtml_Reassignment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('reassignmentGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $dated = date('Y-m-d H:i:s', strtotime('-7 days', time())); //   Mage::getModel('core/date')->gmtDate(NULL, strtotime('-7 days', time());    
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addfieldtofilter('updated_at', array('lteq' => $dated))
            ->setOrder('priority_name','ASC');

        /*prepare list of exclude statuses - apply status filter*/
        $exclude = explode(';', Mage::getStoreConfig('codebase/tickets/billable_statuses'));
        foreach ($exclude as $status) {
            if (!empty($status)) $collection->addFieldToFilter('status_name', array('nlike' => "%{$status}%"));
        }

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
            'header' => Mage::helper('codebase')->__('Codebase Link'),
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

        $reassignTo = array();
        /*$collection = Mage::getModel('codebase/projects')->getCollection();

        $collection->getSelect()->joinLEFT(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.user_id ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_id',array('codebase_users.user_id','codebase_users.user_name'));
        $collection->getSelect()->group('codebase_users.user_id'); 

        foreach ($collection as $user) {
            $reassignTo[] = array('value' => $user->getUserId(), 'label' => $user->getUserName());
        }*/
        $reassignTo = Mage::helper('codebase')->getProductOwners();

        $this->getMassactionBlock()->addItem('reassign_to', array(
            'label'=> Mage::helper('codebase')->__('Reassign to'),
            'url'  => $this->getUrl('*/*/reassignTo', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'reassign_to',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('catalog')->__('Select Option'),
                    'values' => $reassignTo
                )
            )
        ));
        return $this;
    }

}
