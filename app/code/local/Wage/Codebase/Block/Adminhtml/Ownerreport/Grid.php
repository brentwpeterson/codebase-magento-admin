<?php
class Wage_Codebase_Block_Adminhtml_Ownerreport_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    /**
     * Sub report size
     *
     * @var int
     */
    protected $_subReportSize = 0;

    public function __construct()
    {
        parent::__construct();
        $this->setId('codebase_ownerreport_grid');
        //$this->setFilterVisibility(true);
        $this->setTemplate('codebase/ownerreport.phtml');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('codebase/activities_collection');
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('project_name', array(
            'header'    => Mage::helper('codebase')->__('Project Name'),
            'align'     =>'left',
            'width'     => '100px',
            'index'     => 'project_name',
        ));


        $this->addColumn('actor_name', array(
            'header'    => Mage::helper('codebase')->__('Product Owner'),
            'align'     =>'left',
            'width'     => '100px',
            'index'     => 'actor_name',
        ));

        $this->addColumn('ownerupdate', array(
            'header'    => Mage::helper('codebase')->__('Ticket update count'),
            'align'     =>'left',
            'width'     => '100px',
            'index'     => 'ownerupdate',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Ownerreport_Grid_Renderer_Link',
            'projectid'     => 'project_id',
            'actoremail'     => 'actor_email',
            'fromdate'     => strtotime($this->getParamDate('report_from')),
            'todate'     => strtotime($this->getParamDate('report_to'))
        ));

        $this->addColumn('hourslogged', array(
            'header'    => Mage::helper('codebase')->__('Hours Logged'),
            'align'     =>'left',
            'width'     => '100px',
            'index'     => 'hourslogged'
        ));

        $this->addExportType('*/*/exportProjectreportCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportProjectreportExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getReport($from, $to)
    {
        if ($from == '') {
            $from = $this->getFilter('report_from');
        }
        if ($to == '') {
            $to = $this->getFilter('report_to');
        }
        Mage::getSingleton('core/session')->setProject($this->getFilter('report_po'));
        return $this->getCollection()->getReport($from, $to);
    }

    /**
     * Return date periods
     *
     * @return array
     */
    public function getPeriods()
    {
        return array(
            'year'  => Mage::helper('reports')->__('Year')
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }


    public function getProjects()
    {
        $collection = Mage::getModel('codebase/projects')->getCollection()
            ->addFieldToFilter('status','active')
            ->setOrder('project_name','ASC');

        $projects = array();
        $projects[] = Mage::helper('codebase')->__('All Projects');
        foreach ($collection as $project) {
            $projects[$project['project_id']] = $project['project_name'];
        }
        return $projects;
    }

    public function getProductOwnerName($userId)
    {
        $user = Mage::getModel('codebase/users')->getCollection()
            ->addFieldToFilter('user_id', $userId)
            ->getFirstItem()
        ;

        $productOwnerName = $user->getFirstName().' '.$user->getLastName();
        if ($productOwnerName) {
            return $productOwnerName;
        }
    }

    public function getParamDate($param)
    {
        $filter = $this->getParam('filter');
        $filter_data = Mage::helper('adminhtml')->prepareFilterString($filter);     
        return $filter_data[$param];
    }
}
