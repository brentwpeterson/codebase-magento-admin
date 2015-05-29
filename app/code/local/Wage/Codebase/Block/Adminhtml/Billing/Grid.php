<?php
class Wage_Codebase_Block_Adminhtml_Billing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('billingGrid');
        $this->setDefaultSort('main_table.project_name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /*$collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('estimated_time',0)
            ->setOrder('priority_name','ASC');
        */
        $collection = Mage::getModel('codebase/activities')
            ->getCollection()
            ->addFieldToFilter('time_added', array('neq' => 'NULL' ));

        $collection->getSelect()
            //->columns('SUM(time_added) as total_spent_time')
            ->columns('SUM(TIME_TO_SEC(time_added))/60.00 as total_spent_time')
            ->group(array('number','main_table.project_id','actor_email'));

        $collection->getSelect()->joinRight(array('t1' => Mage::getConfig()->getTablePrefix().'codebase_tickets'), 'main_table.project_id = t1.project_id AND main_table.number = t1.ticket_id',array('resolution','permalink'))
            ->where(Mage::getConfig()->getTablePrefix()."t1.resolution = 'open'");

       // echo $collection->getSelect();exit;
        $collection->setOrder('main_table.project_name','ASC');
        //$collection->getSelect()->where('total_spent_time != NULL');
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

        $user_array = array();
        $users = Mage::getModel('codebase/activities')
            ->getCollection();
        $users->getSelect()->group('actor_name');


        foreach($users->getData() as $user){
            $admin = Mage::getModel("admin/user")->getCollection()
                ->addFieldToFilter('email',$user['actor_email'])
                ->getFirstItem();
            if ($admin->getId()) {
                if($admin->getWagentoStaff()){
                    $user_array[$user['actor_name']] = $user['actor_name'];
                }
            }
            unset($admin);
        }

        $this->addColumn('project_name', array(
            'header' => Mage::helper('codebase')->__('Project'),
            'index' => 'main_table.project_name',
            'type'      => 'options',
            'options' => $project_array,
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Billing_Grid_Renderer_Link',
            'permalink' => 'permalink',
            'project_name' => 'project_name'
        ));
        $this->addColumn('actor_name', array(
            'header' => Mage::helper('codebase')->__('User'),
            'index' => 'actor_name',
            'type'      => 'options',
            'options' => $user_array
        ));
        $this->addColumn('number', array(
            'header'    =>Mage::helper('codebase')->__('Ticket Number'),
            'index'     =>'number',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Billing_Grid_Renderer_Ticketlink',
            'permalink' => 'permalink'

        ));
        $this->addColumn('subject', array(
            'header'    =>Mage::helper('codebase')->__('Subject'),
            'index'     =>'subject',
        ));

        $this->addColumn('total_spent_time', array(
            'header' => Mage::helper('codebase')->__('Total Time Used (in Mins.)'),
            'index' => 'total_spent_time',
            'type'      =>'number',

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

    
}
