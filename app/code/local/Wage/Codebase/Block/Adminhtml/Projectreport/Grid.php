<?php
class Wage_Codebase_Block_Adminhtml_Projectreport_Grid extends Mage_Adminhtml_Block_Report_Grid
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
        $this->setId('codebase_projectreport_grid');
        //$this->setFilterVisibility(true);
        $this->setTemplate('codebase/projectreport.phtml');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('codebase/projects_collection');
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

        $this->addColumn('hours_logged', array(
            'header'    => Mage::helper('codebase')->__('Hours Logged (all tickets)'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'open_tickets',
            'frame_callback' => array($this, 'callbackHoursLogged'),
        ));

        $this->addColumn('avg_hours', array(
            'header'    => Mage::helper('codebase')->__('Average Hours Logged'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'avg_hours',
            'frame_callback' => array($this, 'callbackAvgHoursLogged'),
        ));

        $this->addColumn('product_owner', array(
            'header'    => Mage::helper('codebase')->__('Project Owner'),
            'align'     =>'left',
            'width'     => '100px',
            'index'     => 'product_owner',
            'frame_callback' => array($this, 'callbackProductOwner'),
        ));

        $this->addColumn('open_tickets', array(
            'header'    => Mage::helper('codebase')->__('Open Tickets'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'open_tickets',
            'frame_callback' => array($this, 'callbackOpenTickets'),
        ));

        $this->addColumn('wagento_tickets', array(
            'header'    => Mage::helper('codebase')->__('Wagento tickets'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'wagento_tickets',
            'frame_callback' => array($this, 'callbackWagentoTickets'),
        ));

        $this->addColumn('client_tickets', array(
            'header'    => Mage::helper('codebase')->__('Client tickets'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'client_tickets',
            'frame_callback' => array($this, 'callbackClientTickets'),
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
        Mage::getSingleton('core/session')->setReportProdcutOwner($this->getFilter('report_po'));

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

    public function callbackHoursLogged($value, $row, $column, $isExport)
    {
        $projectId = $row->getProjectId();
        $times = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('project_id', $projectId)
            ->addFieldToFilter('session_date', array(
                'from'     => $this->getFilter('report_from'),
                'to'       => $this->getFilter('report_to'),
                'datetime' => true
            ))
        ;

        $loggedMins = 0;
        foreach ($times as $times) {
            $loggedMins += $times->getMinutes();
        }
        if ($loggedHours = Mage::helper('codebase')->convertToHoursMins($loggedMins)) {
            return $loggedHours;
        }
        return "0";
    }

    public function callbackAvgHoursLogged($value, $row, $column, $isExport)
    {
        $projectId = $row->getProjectId();
        $times = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('project_id', $projectId)
            ->addFieldToFilter('session_date', array(
                'from'     => $this->getFilter('report_from'),
                'to'       => $this->getFilter('report_to'),
                'datetime' => true
            ))
        ;

        $loggedMins = 0;
        foreach ($times as $times) {
            $loggedMins += $times->getMinutes();
        }


        $projectId = $row->getProjectId();
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('project_id', $projectId)
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getFilter('report_from'),
                'to'       => $this->getFilter('report_to'),
                'datetime' => true
            ))
        ;
        $totalOpenTickets = count($collection);

        if($loggedMins && $totalOpenTickets)
        {
            $avgLoggedMins = $loggedMins / $totalOpenTickets;
        }
        
        if ($avgLoggedHours = Mage::helper('codebase')->convertToHoursMins($avgLoggedMins)) {
            return $avgLoggedHours;
        }
        return "0";
    }

    public function callbackOpenTickets($value, $row, $column, $isExport)
    {
        $projectId = $row->getProjectId();
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('project_id', $projectId)
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getFilter('report_from'),
                'to'       => $this->getFilter('report_to'),
                'datetime' => true
            ))
        ;
        if ($totalOpenTickets = count($collection)) {
            return $totalOpenTickets;
        }
        return "0";
    }

    public function callbackWagentoTickets($value, $row, $column, $isExport)
    {
        $user = Mage::getModel('codebase/users')->getCollection();
        $user->addFieldToFilter('company', array('eq' => 'Wagento'));

        $assignee =array();
        foreach($user as $item){
            $assignee[] = $item->getUserName();
        }

        $projectId = $row->getProjectId();
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('project_id', $projectId)
            ->addFieldToFilter('assignee', array("in" => $assignee))
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getFilter('report_from'),
                'to'       => $this->getFilter('report_to'),
                'datetime' => true
            ))
        ;
        if ($totalWagentoTickets = count($collection)) {
            return $totalWagentoTickets;
        }
        return "0";
    }

    public function callbackClientTickets($value, $row, $column, $isExport)
    {
        $user = Mage::getModel('codebase/users')->getCollection();
        $user->addFieldToFilter('company', array('neq' => 'Wagento'));

        $assignee =array();
        foreach($user as $item){
            $assignee[] = $item->getUserName();
        }

        $projectId = $row->getProjectId();
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('project_id', $projectId)
            ->addFieldToFilter('assignee', array("in" => $assignee))
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getFilter('report_from'),
                'to'       => $this->getFilter('report_to'),
                'datetime' => true
            ))
        ;
        if ($totalClientTickets = count($collection)) {
            return $totalClientTickets;
        }
        return "0";
    }

    public function callbackProductOwner($value, $row, $column, $isExport)
    {
        $userId = $row->getUserId();
        return $this->getProductOwnerName($userId);

    }

    public function getProductOwners()
    {
        $projects = Mage::getModel('codebase/projects')->getCollection()
            ->addFieldToFilter('status', 'active');
        $productOwner = array();
        $productOwner[] = Mage::helper('codebase')->__('All Product Owner');
        foreach ($projects as $project) {
            if ($userId = $project->getUserId()) {
                $productOwner[$userId] = $this->getProductOwnerName($userId);
            }
        }
        return $productOwner;
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
}
