<?php
class Wage_Codebase_Block_Adminhtml_Clientreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
            ->addFieldToFilter('category_name', array('nin' => $excludeTypes))
            ->addFieldToFilter('status_name', array('neq' => 'Hold: Reason posted in ticket'))
            ->setOrder('project_name','ASC');


        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));

        $user = Mage::getModel('codebase/users')->getCollection();
        $user->addFieldToFilter('company', array('neq' => 'Wagento'));

        $assignee =array();
        foreach($user as $item){
            $assignee[] = $item->getUserName();
        }
        $collection->addFieldToFilter("main_table.assignee",array("in" => $assignee));
        $collection->getSelect()->where("assignee_id = last_status_updater_id");

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
      
        $this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

  
    
    
}
