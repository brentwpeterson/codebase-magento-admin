<?php
class Wage_Codebase_Block_Adminhtml_Viewreport extends Mage_Adminhtml_Block_Template {
   	public function _construct()
        {   	
        	parent::_construct();
        	$this->setTemplate('codebase/viewreport.phtml');
        }

	public function getClient()
	{
		$clientId = $this->getRequest()->getParam('client_id');
       		return Mage::getModel('codebase/users')->getCollection()
						->addFieldToFilter('user_id',$clientId)
						->getFirstItem();
	}

	public function getProductOwner()
	{
 		$userId = $this->getRequest()->getParam('owner_id');
       		return Mage::getModel('codebase/users')->getCollection()
						->addFieldToFilter('user_id',$userId)
						->getFirstItem();
	}

	public function getProject()
	{
		 $permalink = $this->getRequest()->getParam('permalink');
	}
	
	public function getReportHtml()
    {
        $clientId = $this->getRequest()->getParam('client_id');
        $userId = $this->getRequest()->getParam('owner_id');
        $project_permalink = $this->getRequest()->getParam('permalink');

        $productOwner = Mage::getModel('codebase/users')->getCollection()
                            ->addFieldToFilter('user_id',$userId)
                            ->getFirstItem();


        $client = Mage::getModel('codebase/users')->getCollection()
                        ->addFieldToFilter('user_id',$clientId)
                        ->getFirstItem();

        $html = '';
        $html .= "<h3>Hello, ".$client->getFirstName().' '.$client->getLastName()."</h3>";
        $html .= "<p>Following tickets are in que for your project,</p>";
        $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;margin:20px 0;">
                         <tr>
                          <th>'.$this->__('Ticket').'</th>
                          <th>'.$this->__('Summary').'</th>
                          <th>'.$this->__('Priority').'</th>
                          <th>'.$this->__('Status').'</th>
                        </tr>';

        $tickets = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getReportFromDateWithFormat(),
                'to'       => $this->getReportToDateWithFormat(),
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project_permalink)
            ->setOrder('priority_name', 'ASC')
        ;
        foreach ($tickets as $ticket) {
            $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$project_permalink.'/tickets/'.$ticket->getTicketId();
            $html .= '<tr><td><a target="_blank" href="'.$url.'">'.$project_permalink.'-'.$ticket->getTicketId().'</a><br/>';
            $html .= '<td align="center">'.$ticket->getSummary().'</td>';
            $html .= '<td align="center">'.$ticket->getPriorityName().'</td>';
            $html .= '<td align="center">'.$ticket->getstatusName().'</td></tr>';
        }


        $html .= '</table>';

        // get total hours logged
        $totalHoursLogged = $this->getLoggedTime($project_permalink);
        $html .= '<p><b>'.$this->__('Total Hours Logged: ').$totalHoursLogged.'</b></p>';

        // get completed tickets
        $completedTickets = $this->getCompletedTickets($project_permalink);
        $html .= '<p><b>'.$this->__('Tickets Completed: ').$completedTickets.'</b></p>';

        // get new tickets
        $newTickets = $this->getNewTickets($project_permalink);
        $html .= '<p><b>'.$this->__('New Tickets Created: ').$newTickets.'</b></p>';

        // Total Hours Left (From Estimations)
        $totalHoursLeftFromEstimation = $this->getTotalHoursLeftEstimation($project_permalink);
        $html .= '<p><b>'.$this->__('Total Hours Left (From Estimations): ').$totalHoursLeftFromEstimation.'</b></p>';

        // get Estimation completion date
        $estCompletionDate = $this->getEstCompletionDate($totalHoursLeftFromEstimation);
        $html .= '<p><b>'.$this->__('Estimation Completion Date: ').$estCompletionDate.'</b></p>';

        return $html;
	}

    public function getNewTickets($project_permalink)
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('created_at', array(
                'from'     => $this->getReportFromDateWithFormat(),
                'to'       => $this->getReportToDateWithFormat(),
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project_permalink)
        ;

        return count($collection->getData());
    }

    public function getCompletedTickets($project_permalink)
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','close')
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getReportFromDateWithFormat(),
                'to'       => $this->getReportToDateWithFormat(),
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project_permalink)
        ;

        return count($collection->getData());
    }

    public function getLoggedTime($project_permalink)
    {
        $project = Mage::getModel('codebase/projects')->loadProjectByPermalink($project_permalink);
        $projectId = $project->getProjectId();
        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getReportFromDateWithFormat(),
                'to'       => $this->getReportToDateWithFormat(),
                'datetime' => true
            ))
            ->addFieldToFilter('created_at', array(
                'from'     => $this->getReportFromDateWithFormat(),
                'to'       => $this->getReportToDateWithFormat(),
                'datetime' => true
            ))
            ->addFieldToFilter('project_id', $projectId)
        ;
        $collection->getSelect()
            //->columns('SUM(time_added) as total_spent_time')
            ->columns('SUM(TIME_TO_SEC(minutes)) as total_logged_time');

        $data = $collection->getData();
        return Mage::helper('codebase')->convertToHoursMins($data[0]['total_logged_time']);
    }

    public function getTotalHoursLeftEstimation($project_permalink)
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('updated_at', array(
                'from'     => $this->getReportFromDateWithFormat(),
                'to'       => $this->getReportToDateWithFormat(),
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project_permalink)
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

    public function getEstCompletionDate($totalHoursLeftFromEstimation)
    {
        $totalHoursLeftFromEstimation = explode(':', $totalHoursLeftFromEstimation);
        $noOfDaysToComplete = round(($totalHoursLeftFromEstimation[0] * 4)/ 8);
        $startDate = date("Y-m-d", Mage::getModel('core/date')->timestamp(time()));
        $holidays = array(); //ex. [new DateTime("2014-06-01"), new DateTime("2014-06-02")]
        $nonBusinessDays = array(6, 7);

        $estCompletionDate = Mage::getModel('codebase/businessdayscalculator')
            ->addBusinessDays($startDate, $holidays, $nonBusinessDays, $noOfDaysToComplete);
        //echo $noOfDaysToComplete; exit;

        return $estCompletionDate;
    }

    public function getReportFromDate()
    {
        if ($reportFromDate = Mage::getSingleton('admin/session')->getCodebaseReportFromDate()) {
            $reportFromDate = date("m/d/Y", strtotime($reportFromDate));
            return $reportFromDate;
        }
    }

    public function getReportToDate()
    {
        if ($reportToDate = Mage::getSingleton('admin/session')->getCodebaseReportToDate()) {
            $reportToDate = date("m/d/Y", strtotime($reportToDate));
            return $reportToDate;
        }
    }

    public function getReportFromDateWithFormat()
    {
        if ($reportFromDate = Mage::getSingleton('admin/session')->getCodebaseReportFromDate()) {
            return $reportFromDate;
        }
    }

    public function getReportToDateWithFormat()
    {
        if ($reportToDate = Mage::getSingleton('admin/session')->getCodebaseReportToDate()) {
            return $reportToDate;
        }
    }

    public function showReport()
    {
        if ($this->getReportFromDate() && $this->getReportToDate()) {
            return true;
        }
        return false;
    }

}
