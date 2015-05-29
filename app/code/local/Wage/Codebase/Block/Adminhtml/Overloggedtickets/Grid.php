<?php
class Wage_Codebase_Block_Adminhtml_Overloggedtickets_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('overloggedticketsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('total_time_spent', array('gteq' => 480 ))
            ->setOrder('priority_name','ASC');

        $collection->getSelect()->columns('cast( ((total_time_spent)/60) as decimal(10,2)) as total_spent_time_by_hours, cast( ((estimated_time)/60) as decimal(10,2)) as estimated_time_by_hours');
        //$collection->getSelect()->columns('cast( ((estimated_time)/60) as decimal(10,2)) as estimated_time_by_hours');

              /*prepare list of exclude statuses - apply status filter*/
        $exclude = explode(';', Mage::getStoreConfig('codebase/tickets/billable_statuses'));
        foreach ($exclude as $status) {
            if (!empty($status)) $collection->addFieldToFilter('status_name', array('nlike' => "%{$status}%"));
        }

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('project_name', array(
            'header' => Mage::helper('codebase')->__('Project'),
            'index' => 'project_name',

        ));
       
        $this->addColumn('assignee', array(
            'header'    =>Mage::helper('codebase')->__('Assignee'),
            'index'     =>'assignee',
        ));
        $this->addColumn('wagento_staff', array(
            'header'    =>Mage::helper('codebase')->__('Wagento Staff'),
            'index'     =>'wagento_staff',
            'type'      => 'options',
            'options'   => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackWagentoStaff'),
            'filter_condition_callback' => array($this, '_filterConditionWagentoStaff')
        ));
        $this->addColumn('summary', array(
            'header' => Mage::helper('codebase')->__('Summary'),
            'index' => 'summary',

        ));
        $this->addColumn('status_name', array(
            'header' => Mage::helper('codebase')->__('Status'),
            'index' => 'status_name',

        ));
        $this->addColumn('total_spent_time_by_hours', array(
            'header' => Mage::helper('codebase')->__('Spent(Hrs.)'),
            'index' => 'total_spent_time_by_hours',
        ));

        $this->addColumn('estimated_time_by_hours', array(
            'header' => Mage::helper('codebase')->__('Estimated(Hrs.)'),
            'index' => 'estimated_time_by_hours',
        ));

        $this->addColumn('estimate_status', array(
            'header' => Mage::helper('codebase')->__('Logged Work'),
            'index' => 'estimated_time_by_hours',
            'spentindex' => 'total_spent_time_by_hours',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Overloggedtickets_Grid_Renderer_Estimation',
            'filter_condition_callback' => array($this, '_filterConditionEstimateStatus'),
            'type'      => 'options',
            'options'   => array(
                'Over Estimation' => 'Over Estimation',
                'Under Estimation' => 'Under Estimation',
                'Equal Estimation' => 'Equal Estimation',
            ),
        ));

        $this->addColumn('Codebase', array(
            'header' => Mage::helper('codebase')->__('Codebase Link'),
            'align' => 'left',
            'index' => 'ticket_id',
            'width'     => '70',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Overloggedtickets_Grid_Renderer_Link',
            'permalink' => 'permalink'
        ));

        $this->addColumn('Updates', array(
            'header' => Mage::helper('codebase')->__('Updates'),
            'align' => 'left',
            'index' => 'comments'
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
        //$this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('cancel_order', array(
            'label'=> Mage::helper('codebase')->__('Close'),
            'url'  => $this->getUrl('*/*/updateStatus'),
        ));

        $collection = Mage::getModel('codebase/priorities')->getCollection()
                                        ->addFieldToSelect('name');

        foreach($collection->getData() as $item){
            $priorities[] = $item['name'];
        }
        array_unique($priorities);
        $priorities = array_combine($priorities,$priorities );
        array_unshift($priorities,'') ;
        $this->getMassactionBlock()->addItem('priority', array(
            'label'=> Mage::helper('codebase')->__('Change Priority'),
            'url'  => $this->getUrl('*/*/updatePriority', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'priority',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('catalog')->__('Select Priority'),
                    'values' => $priorities
                )
            )
        ));
        return $this;
    }

    public function callbackWagentoStaff($value, $row, $column, $isExport)
    {
        $assignee = $row->getAssignee();
        $users = Mage::getModel("codebase/users")
            ->getCollection()
            ->addFieldToFilter('user_name', $assignee)
        ;

        if ($users->getFirstItem()->getCompany() == 'Wagento') {
            return $this->__('Yes');
        } else {
            return $this->__('No');
        }
    }

    protected function _filterConditionWagentoStaff($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == NULL) {
            return $this;
        } else {
            if ($value) {
                
                $user = Mage::getModel('codebase/users')->getCollection();
                $user->addFieldToFilter('company', array('eq' => 'Wagento'));

                $assignee =array();
                foreach($user as $item){
                    $assignee[] = $item->getUserName();
                }
                $this->getCollection()->addFieldToFilter("main_table.assignee",array("in" => $assignee));
                return $this;

            } else {

                $user = Mage::getModel('codebase/users')->getCollection();
                  $user->addFieldToFilter('company', array('neq' => 'Wagento'));
             
                  $assignee =array();
                  foreach($user as $item){
                    $assignee[] = $item->getUserName();
                  }
                $this->getCollection()->addFieldToFilter("main_table.assignee",array("in" => $assignee));
                return $this;
            }
        }
    }

    protected function _filterConditionEstimateStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == NULL) {
            return $this;
        } else {
            if ($value) {

                if($value == "Over Estimation")
                {
                $this->getCollection()->addFieldToFilter('total_time_spent', array ('gt' => new Zend_Db_Expr('estimated_time') ) );
                } elseif ($value == "Under Estimation"){
                    $this->getCollection()->addFieldToFilter('estimated_time', array ('gt' => new Zend_Db_Expr('total_time_spent') ) );
                } else {
                    $this->getCollection()->addFieldToFilter('estimated_time', array ('eq' => new Zend_Db_Expr('total_time_spent') ) );
                }

                return $this;

            }
        }
    }
}
