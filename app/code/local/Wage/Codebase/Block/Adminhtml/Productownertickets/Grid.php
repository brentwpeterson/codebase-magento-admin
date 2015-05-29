<?php
class Wage_Codebase_Block_Adminhtml_Productownertickets_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productownerticketsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $productOwner = Mage::helper('codebase')->getProductOwners();//explode(',',Mage::getStoreConfig('codebase/backlog/project_owner'));
        $productOwner = array_keys($productOwner);
        if(count($productOwner) >= 1){    
            $dated = date('Y-m-d H:i:s', strtotime('-1 days', time())); 
            $collection = Mage::getModel('codebase/tickets')->getCollection()
                ->addFieldToFilter('resolution','open')
                ->addFieldToFilter('updated_at', array('lteq' => $dated))
                ->setOrder('priority_name','ASC');

            /*prepare list of exclude statuses - apply status filter*/
            $exclude = explode(';', Mage::getStoreConfig('codebase/tickets/billable_statuses'));
            foreach ($exclude as $status) {
                if (!empty($status)) $collection->addFieldToFilter('status_name', array('nlike' => "%{$status}%"));
            }

            $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'=>'company'))
                ->where('company = "Wagento" ');

            /*get apply active projects filter if there is data*/
            $active = Mage::getModel('codebase/projects')->getActiveIds();
            if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));




            $collection->addFieldToFilter('codebase_users.user_id' , array('in' => $productOwner));
        }    

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
        $this->addColumn('estimated_time', array(
            'header' => Mage::helper('codebase')->__('Est. (Mins.)'),
            'index' => 'estimated_time',
            'filter_condition_callback' => array($this, '_filterConditionCallback')

        ));
        $this->addColumn('total_time_spent', array(
            'header' => Mage::helper('codebase')->__('Spent(Mins.)'),
            'index' => 'total_time_spent',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Tickets_Grid_Renderer_Timespent',
            'permalink' => 'permalink',
            'ticket_id' => 'ticket_id',
        ));
        $this->addColumn('time_left', array(
            'header' => Mage::helper('codebase')->__('Left(Mins.)'),
            'index' => 'time_left',

        ));
        $this->addColumn('Codebase', array(
            'header' => Mage::helper('codebase')->__('Codebase Link'),
            'align' => 'left',
            'index' => 'ticket_id',
            'width'     => '70',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Tickets_Grid_Renderer_Link',
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
                /*
                $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users',
                    'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',
                    array('company'))
                    ->where(Mage::getConfig()->getTablePrefix()."codebase_users.company = 'Wagento'");
                */

            } else {

                $user = Mage::getModel('codebase/users')->getCollection();
                  $user->addFieldToFilter('company', array('neq' => 'Wagento'));
             
                  $assignee =array();
                  foreach($user as $item){
                    $assignee[] = $item->getUserName();
                  }
                $this->getCollection()->addFieldToFilter("main_table.assignee",array("in" => $assignee));
                return $this;
                /*
                $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users',
                    'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',
                    array('company'))
                    ->where(Mage::getConfig()->getTablePrefix()."codebase_users.company != 'Wagento'");
                */

            }
        }
    }
}
