<?php
class Wage_Codebase_Block_Adminhtml_Generatereport extends Mage_Adminhtml_Block_Template {
   	public function _construct()
        {   	
        	parent::_construct();
        	$this->setTemplate('codebase/generatereport.phtml');
        }

    public function getFrom() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['from'])) {
            return $filter['from'];
        }

        return null;
    }

    public function getTo() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['to'])) {
            return $filter['to'];
        }

        return null;
    }

    public function getProject() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['project'])) {
            return $filter['project'];
        }

        return null;
    }

    public function getProductOwner() {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['project'])) {
            $projectId =  $filter['project'];
            $project = Mage::getModel('codebase/projects')->loadProjectByProjectId($projectId);
            $productOwnerId = $project->getUserId();
            $user = Mage::getModel('codebase/users')->findUser($productOwnerId);
            return $user;
        }

        return null;
    }

    public function getActiveTickets()
    {
        $fromDate = $this->getFrom();
        $toDate   = $this->getTo();
        $project  = Mage::getModel('codebase/projects')->loadProjectByProjectId($this->getProject());
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('permalink', $project->getPermalink());

        return count($collection->getData());
    }

    public function getCreatedTickets()
    {
        $fromDate = $this->getFrom();
        $toDate   = $this->getTo();
        $project  = Mage::getModel('codebase/projects')->loadProjectByProjectId($this->getProject());
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('created_at', array(
                'from'     => $fromDate,
                'to'       => $toDate,
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project->getPermalink());

        return count($collection->getData());
    }

    public function getClosedTickets()
    {
        $fromDate = $this->getFrom();
        $toDate   = $this->getTo();
        $project  = Mage::getModel('codebase/projects')->loadProjectByProjectId($this->getProject());
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','close')
            ->addFieldToFilter('updated_at', array(
                'from'     => $fromDate,
                'to'       => $toDate,
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project->getPermalink());

        return count($collection->getData());
    }

    public function getLoggedTime()
    {
        $fromDate = $this->getFrom();
        $toDate   = $this->getTo();
        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('session_date', array(
                'from'     => $fromDate,
                'to'       => $toDate,
                'datetime' => true
            ))
            ->addFieldToFilter('project_id', $this->getProject())
        ;

        $collection->getSelect()
            //->columns('SUM(time_added) as total_spent_time')
            ->columns('SUM(minutes) as total_logged_time');
        $data = $collection->getData();
        return Mage::helper('codebase')->convertToHoursMins($data[0]['total_logged_time']);

    }

    public function getTimeTrackingEntries()
    {
        $fromDate = $this->getFrom();
        $toDate   = $this->getTo();
        if($fromDate && $toDate)
        {
        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('session_date', array(
                'from'     => $fromDate,
                'to'       => $toDate,
                'datetime' => true
            ))
            ->addFieldToFilter('project_id', $this->getProject())
            ->setOrder('session_date','ASC');
            return $collection;
        }
    }
}
