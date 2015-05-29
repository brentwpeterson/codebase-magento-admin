<?php
class Wage_Codebase_Block_Adminhtml_Projects_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('projectsGrid');
      $this->setDefaultSort('main_table.project_name');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('codebase/projects')->getCollection()
                    ->addFieldToFilter('status','active')
                    ->setOrder('project_name','ASC');



      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {


      $this->addColumn('project_name', array(
          'header'    => Mage::helper('codebase')->__('Project Name'),
          'align'     =>'left',
          'width'     => '100px',
          'index'     => 'project_name',
          'filter_index'=>'main_table.project_name',
          'type'      => 'options',
          'options' => $this->getProjects()

      ));

      /*$this->addColumn('product_id', array(
          'header'    => Mage::helper('codebase')->__('Product'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'product_id',
          'renderer' => 'Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Productlink',
      ));*/
      $this->addColumn('client_id', array(
          'header'    => Mage::helper('codebase')->__('Client'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'client_id',
          'renderer' => 'Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Client',
          'filter_condition_callback' => array($this, '_clientFilter')
      ));

      $this->addColumn('Owner', array(
          'header'    => Mage::helper('codebase')->__('Product Owner'),
          'align'     =>'left',
          'width'     => '50px',
          'user_id'     => 'user_id',
          'renderer' => 'Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Ownerinfo',
          'filter_condition_callback' => array($this, '_productownerFilter')
      ));

      $this->addColumn('Techlead', array(
          'header'    => Mage::helper('codebase')->__('Tech Lead'),
          'align'     =>'left',
          'width'     => '50px',
          'techlead_id'     => 'techlead_id',
          'renderer' => 'Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Techlead',
          'filter_condition_callback' => array($this, '_teachleadFilter')
      ));

      /*$this->addColumn('Status', array(
          'header'    => Mage::helper('codebase')->__('Status'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'status',
      ));*/

      $this->addColumn('Last time client contact at', array(
          'header'    => Mage::helper('codebase')->__('Last time client contact at'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'last_client_contact',
          'filter' => false,
          'sortable'  => false,
      ));

      $this->addColumn('open_tickets', array(
          'header'    => Mage::helper('codebase')->__('Number of open tickets'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'open_tickets',
          'filter' => false,
          'sortable'  => false,
          'frame_callback' => array($this, 'callbackOpenTickets'),
      ));

      $this->addColumn('assigned_developers', array(
          'header'    => Mage::helper('codebase')->__('Number of assigned developers'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'assigned_developers',
          'filter' => false,
          'sortable'  => false,
          'frame_callback' => array($this, 'callbackAssignedDeveloper'),
      ));

      $this->addColumn('estimated_hours', array(
          'header'    => Mage::helper('codebase')->__('Estimated hours'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'estimated_hours',
          'filter' => false,
          'sortable'  => false,
          'frame_callback' => array($this, 'callbackEstimatedHours'),
      ));

      $this->addColumn('completion_date', array(
          'header'    => Mage::helper('codebase')->__('Completion Date'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'completion_date',
          'filter' => false,
          'sortable'  => false,
          'frame_callback' => array($this, 'callbackCompletionDate'),
      ));

      /*$this->addColumn('Last report sent at', array(
          'header'    => Mage::helper('codebase')->__('Last report sent at'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'last_report_sent_at',
      ));*/

      $this->addColumn('report', array(
          'header'    => Mage::helper('codebase')->__('Report'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'client_id',
          'user_id'   => 'user_id',
          'permalink'   => 'permalink',
          'filter' => false,
          'sortable'  => false,
          'renderer' => 'Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Sendreport',
      ));
      /*$this->addColumn('owner_name', array(
          'header'    => Mage::helper('codebase')->__('Owner Name'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'owner_name',
      ));

      $this->addColumn('owner_email', array(
          'header'    => Mage::helper('codebase')->__('Owner Email'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'owner_email',
      ));

      $this->addColumn('owner_phone', array(
          'header'    => Mage::helper('codebase')->__('Owner Phone'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'owner_phone',
      ));
	*/
		$this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    

    public function getRowUrl($row)
    {
          return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function callbackAssignedDeveloper($value, $row, $column, $isExport)
    {
        $projectId = $row->getProjectId();
        $projectDevelopers = Mage::getModel("codebase/projectindex")->getCollection()
            ->addFieldToFilter('project_id', $projectId);

        $projectDevelopers->getSelect()->join(array('t1' => Mage::getConfig()->getTablePrefix().'codebase_users'), 'main_table.user_id = t1.user_id')
            ->where("t1.enabled = '1' AND t1.company = 'Wagento'");

        return count($projectDevelopers);
    }

    public function callbackEstimatedHours($value, $row, $column, $isExport)
    {
        $projectId = $row->getProjectId();
        return $this->getTotalCompletionTime($projectId);
    }

    public function callbackCompletionDate($value, $row, $column, $isExport)
    {
        $projectId = $row->getProjectId();
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('project_id', $projectId)
        ;

        $total = 0;
        foreach($collection as $ticket){
            if($ticket->getEstimatedTime() && ($ticket->getEstimatedTime() >= $ticket->getTotalTimeSpent()))
            {
                $total = $total + ($ticket->getEstimatedTime() - $ticket->getTotalTimeSpent());
            }
        }

        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('project_id', $projectId)
        ;
        $collection->getSelect()->group('user_id');

        $developerCount = (int) count($collection->getData());

        $totalEstimationTime = (float) str_replace(":",".",$this->getTotalCompletionTime($projectId));

        $perDayHour = $developerCount * 1 * 6;

        if ($perDayHour) {
            $howManyDays = round($totalEstimationTime / $perDayHour);

            $nonBusinessDays = array('6','7');
            $startDate = date("Y-m-d");
            $holidays = array();
            $businessdate = Mage::getModel('codebase/businessdayscalculator')->addBusinessDays($startDate, $holidays, $nonBusinessDays, $howManyDays);

            return $businessdate;
        }
    }

    public function getTotalCompletionTime($projectId)
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('project_id', $projectId)
        ;

        $total = 0;
        foreach($collection as $ticket){
            if($ticket->getEstimatedTime() && ($ticket->getEstimatedTime() >= $ticket->getTotalTimeSpent()))
            {
                $total = $total + ($ticket->getEstimatedTime() - $ticket->getTotalTimeSpent());
            }
        }
        return Mage::helper('codebase')->convertToHoursMins($total);
    }

    public function callbackOpenTickets($value, $row, $column, $isExport)
    {
        $projectId = $row->getProjectId();
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('project_id', $projectId)
        ;
        if ($totalOpenTickets = count($collection)) {
            return $totalOpenTickets;
        }
        return "0";
    }

    public function getProjects()
    {
        $projects = Mage::getModel('codebase/projects')->getCollection()
            ->addFieldToFilter('status','active')
            ->setOrder('project_name','ASC');

        $projectsName = array();
        foreach ($projects as $project) {
            $projectsName[$project->getProjectName()] = $project->getProjectName();
        }
        return $projectsName;
    }

    protected function _clientFilter($collection, $column)
    {
      if (!$value = $column->getFilter()->getValue()) {
        return $this;
      }
      $user = Mage::getModel('codebase/users')->getCollection();
      $user->addFieldToFilter(
                  array(
                      'first_name',
                      'last_name'
                  ),
                  array(
                      array('like' => '%'.$value.'%'),
                      array('like' => '%'.$value.'%'),
                  )
              );
 
      $ids =array();
      foreach($user as $item){
        $ids[] = $item->getUserId();
      }
        
      $this->getCollection()->addFieldToFilter("main_table.client_id",array("in" => $ids));
      return $this;
    }

    protected function _productownerFilter($collection, $column)
    {
      if (!$value = $column->getFilter()->getValue()) {
        return $this;
      }
      $user = Mage::getModel('codebase/users')->getCollection();
      $user->addFieldToFilter(
                  array(
                      'first_name',
                      'last_name'
                  ),
                  array(
                      array('like' => '%'.$value.'%'),
                      array('like' => '%'.$value.'%'),
                  )
              );
 
      $ids =array();
      foreach($user as $item){
        $ids[] = $item->getUserId();
      }
        
      $this->getCollection()->addFieldToFilter("main_table.user_id",array("in" => $ids));
      return $this;
    }

    protected function _teachleadFilter($collection, $column)
    {
      if (!$value = $column->getFilter()->getValue()) {
        return $this;
      }
      $user = Mage::getModel('codebase/users')->getCollection();
      $user->addFieldToFilter(
                  array(
                      'first_name',
                      'last_name'
                  ),
                  array(
                      array('like' => '%'.$value.'%'),
                      array('like' => '%'.$value.'%'),
                  )
              );
 
      $ids =array();
      foreach($user as $item){
        $ids[] = $item->getUserId();
      }
        
      $this->getCollection()->addFieldToFilter("main_table.techlead_id",array("in" => $ids));
      return $this;
    }
}
