<?php
class Wage_Codebase_Block_Adminhtml_Development_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('developmentGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->setOrder('project_name','ASC');

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));

        $codebaseUser = Mage::getSingleton('core/resource')->getTableName('codebase/users');
        $collection->getSelect()->join(
            array('codebase_users'=> $codebaseUser),
            'main_table.assignee = codebase_users.user_name',
            array('user_name', 'user_id')
        );

        /*$codebaseTeam = Mage::getSingleton('core/resource')->getTableName('codebase/teams');
        $collection->getSelect()->joinLeft(
            array('codebase_teams'=> $codebaseTeam),
            'codebase_users.user_id = 103535',
            array('team_name','members')
        );*/

        $collection->addFieldToFilter('company','Wagento');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $userArray = array();
        $users = Mage::getModel("codebase/users")->getCollection()
            ->addFieldToFilter('company','Wagento')
        ;

        foreach ($users as $user) {
            $userArray[$user->getUserName()] = $user->getFirstName()." ".$user->getLastName();
        }

        $teamsArray = array();
        $teams = Mage::getModel("codebase/teams")->getCollection();

        foreach ($teams as $team) {
            $teamsArray[$team->getTeamId()] = $team->getTeamName();
        }

        $this->addColumn('project_name', array(
            'header' => Mage::helper('codebase')->__('Project'),
            'index' => 'project_name',

        ));
        $this->addColumn('summary', array(
            'header' => Mage::helper('codebase')->__('Summary'),
            'index' => 'summary',

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
            'filter_condition_callback' => array($this, '_filterConditionEst')

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
        $this->addColumn('ticket_id', array(
            'header' => Mage::helper('codebase')->__('Codebase Link'),
            'align' => 'left',
            'index' => 'ticket_id',
            'width'     => '70',
            'renderer' => 'Wage_Codebase_Block_Adminhtml_Development_Grid_Renderer_Link',
            'permalink' => 'permalink'
        ));
        $this->addColumn('user_name', array(
            'header' => Mage::helper('codebase')->__('Developer'),
            'index' => 'user_name',
            'type'      => 'options',
            'options' => $userArray
        ));
        $this->addColumn('team_name', array(
            'header' => Mage::helper('codebase')->__('Team'),
            //'index' => 'team_name',
            'type'      => 'options',
            'options' => $teamsArray,
            'frame_callback' => array($this, 'callbackTeam'),
            'filter_condition_callback' => array($this, '_filterConditionTeam')
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('codebase')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('codebase')->__('XML'));
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function callbackTeam($value, $row, $column, $isExport)
    {
        $userId = $row->getUserId();
        $teams = Mage::getModel("codebase/teams")->getCollection();
        foreach ($teams as $team) {
            $members = explode(',', $team->getMembers());
            if (in_array($userId, $members)) {
                return $team->getTeamName();
            }
        }
        return ;
    }

    protected function _filterConditionTeam($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == NULL) {
            return $this;
        } else {
            $teams = Mage::getModel("codebase/teams")->getCollection()
                ->addFieldToFilter('team_id', $value);
            $members = $teams->getFirstItem()->getMembers();
            $members = explode(',', $members);
            $this->getCollection()
                ->addFieldToFilter('user_id',array('in'=> $members));
        }

        return $this;
    }

    protected function _filterConditionEst($collection, $column)
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
