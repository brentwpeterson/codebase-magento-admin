<?php
class Wage_Codebase_Block_Adminhtml_Developercapacity extends Mage_Adminhtml_Block_Template 
{
    public function _construct()
    {       
        parent::_construct();
        $this->setTemplate('codebase/developercapacity.phtml');
    }

    public function getTeams() 
    { 
        $teams = Mage::app()->getRequest()->getParam('teams'); 
        if($teams){ 
            return $teams; 
        } 
        return ''; 
    } 

    public function getUserTeam() 
    { 
        $teams = Mage::getModel("codebase/teams")->getCollection(); 
        foreach ($teams as $team) { 
            $members = explode(',', $team->getMembers()); 
            foreach ($members as $member) { 
                $teamId[$member] = $team->getTeamName(); 
            } 
        } 
        return $teamId; 
    } 

    public function getAllDeveloperData()
    {
        $teams = $this->getUserTeam();

        $roles = Mage::getModel('admin/roles')->getCollection();
            $roles->addFieldToFilter('role_name', array('in' => array('Development - Frontend','Development - Backend')));
        foreach($roles as $role){
            $adminroles = Mage::getModel('admin/roles')->load($role->getRoleId())->getRoleUsers();
            foreach($adminroles as $adminuserid){
                $adminuserModel = Mage::getModel('admin/user')->load($adminuserid);
                $developerUser[] = $adminuserModel->getApiUser();
                $rolesUser[$adminuserModel->getApiUser()] = $role->getRoleName();
            }   
        }

        $developerCollection = Mage::getModel("codebase/users")
          ->getCollection()
          ->addFieldToFilter('user_name', array('in' => $developerUser));
        ;
        foreach($developerCollection as $developerValues){

            if($this->getTeams()){ 
                if($teams[$developerValues->getUserId()] != $this->getTeams()) 
                    continue; 
            } 

            $developerNames = $developerValues->getFirstName().' '.$developerValues->getLastName();
            $developerArray[$developerValues->getUserId()]['name'] = $developerNames;
            $developerArray[$developerValues->getUserId()]['roles'] = explode(' - ',$rolesUser[$developerValues->getUserName()])[1];
            $developerArray[$developerValues->getUserId()]['tickets'] = count($this->getDevelopersOpenTickets($developerValues->getUserId()));
            $developerArray[$developerValues->getUserId()]['estimated'] = $this->getDeveloperEstimatedHours($developerValues->getUserId());
            $developerArray[$developerValues->getUserId()]['completion'] = $this->getDevelopersCompletionDate($developerValues->getUserId());
            $developerArray[$developerValues->getUserId()]['logged'] = $this->getDeveloperLoggedTime($developerValues->getUserId());
            $developerArray[$developerValues->getUserId()]['lefttime'] = $this->getDeveloperLeftTime($developerValues->getUserId());
            $developerArray[$developerValues->getUserId()]['team'] = $teams[$developerValues->getUserId()];  
        }

        return $developerArray;
    }

    public function getDevelopersOpenTickets($userId)
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
                        ->addFieldToFilter('resolution','open');

        $exclude = explode(';', Mage::getStoreConfig('codebase/tickets/billable_statuses'));
        foreach ($exclude as $status) {
            if (!empty($status)) $collection->addFieldToFilter('status_name', array('nlike' => "%{$status}%"));
        }

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
 
        $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users',
            'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',
            array('company'))
            ->where(Mage::getConfig()->getTablePrefix()."codebase_users.company = 'Wagento'")
            ;

        if($userId){    
            $collection->addFieldToFilter('codebase_users.user_id' , $userId);
        }

        return $collection;
    }

     public function getDeveloperEstimatedHours($userId)
    {
        $collection = $this->getDevelopersOpenTickets($userId);

        $total = 0;
        $data = $collection->getData();
        foreach($data as $ticket){
            if($ticket['estimated_time'] && ($ticket['estimated_time'] >= $ticket['total_time_spent']))
            {
                $total = $total + ($ticket['estimated_time'] - $ticket['total_time_spent']);
            }
        }

        return Mage::helper('codebase')->convertToHoursMins($total);
    }

    public function getDevelopersCompletionDate($userId)
    {
        $collection = $this->getDevelopersOpenTickets($userId);
        if(count($collection) > 0 && $this->getDeveloperEstimatedHours($userId) != NULL)
        {
            $totalEstimationTime = (float) str_replace(":",".",$this->getDeveloperEstimatedHours($userId));

            $perDayHour = 6;

            $howManyDays = round(($totalEstimationTime) / $perDayHour);

            $nonBusinessDays = array('6','7');
            $startDate = date("Y-m-d");
            $holidays = array();
            $businessdate = Mage::getModel('codebase/businessdayscalculator')->addBusinessDays($startDate, $holidays, $nonBusinessDays, $howManyDays);
            
            return $businessdate;
        }
    }


    public function getDeveloperLoggedTime($userId)
    {
        $ticketCollection = $this->getDevelopersOpenTickets($userId);
        foreach($ticketCollection as $ticket){
            $tickets[] = $ticket->getTicketId();
        }

        $collection = Mage::getModel('codebase/timetracking')->getCollection();
            
        if($userId){
            $collection->addFieldToFilter('user_id', $userId);
            $collection->addFieldToFilter('ticket_id', array('in' => $tickets));
        }

        $collection->getSelect() 
            ->columns('SUM(minutes) as total_logged_time');

        $data = $collection->getData();
        return Mage::helper('codebase')->convertToHoursMins($data[0]['total_logged_time']);
    }

    public function getDeveloperLeftTime($userId)
    {
        $collection = $this->getDevelopersOpenTickets($userId);

        $estimated_time = 0;
        $total_time_spent = 0;
        $data = $collection->getData();
        foreach($data as $ticket){
            if($ticket['estimated_time'] && ($ticket['estimated_time'] >= $ticket['total_time_spent']))
            {
                $estimated_time = $estimated_time + $ticket['estimated_time'];
                $total_time_spent = $total_time_spent + $ticket['total_time_spent'];
            } 
        }

        $total = $estimated_time - $total_time_spent;
        /*$data = $collection->getData();
        foreach($data as $ticket){
            $total = $total + $ticket['time_left'];
        }*/
        if($total > 0){
            return Mage::helper('codebase')->convertToHoursMins(abs($total));
        }elseif($total < 0){
            return '+'.Mage::helper('codebase')->convertToHoursMins(abs($total));
        }
        
    }

    public function getAllDeveloperReports($since)
    {
        $query = 'sort:priority status:open';
        $overAllTime = 0;
        $developers = 0;
        $actors = array();
        $utcUsers = array();
        $utcUsers = explode(',', Mage::getStoreConfig('codebase/report/utc_users'));
        $email = explode(',',Mage::getStoreConfig('codebase/report/dailyreport_ids'));
        $limit = 10000;
        //For US and Latin America Office


        $activities = Mage::getModel('codebase/codebase')->activity($query,$limit,$since); //product_shortcode,query
        $activities = array_reverse($activities);

        $allItems = array();
        foreach ($activities as $activity)
        {
            if($activity['type'] == 'ticketing_note')
            {
                $data = array();

                $data['actor_name'] = $activity['actor-name'];   
                if($activity['raw-properties']['changes']['estimated-time-string']) {
                    $time_format = explode(':',$activity['raw-properties']['changes']['estimated-time-string']['estimated-time-string'][1]);
                    if($time_format[1]) {
                        $time  = Mage::helper('codebase')->convertHoursToMinutes($activity['raw-properties']['changes']['estimated-time-string']['estimated-time-string'][1]);
                    } else {
                        $time = $activity['raw-properties']['changes']['estimated-time-string']['estimated-time-string'][1];
                    }

                    $data['estimatetime'] = $time;

                }
                if($data['estimatetime']){
                    if (in_array($activity['actor-email'], $utcUsers)) {
                        // Fall in different timezone
                    } else {
                        $allItems[$activity['actor-email']][] = $data;

                    }
                }
            }
        }
        foreach($allItems as $email => $item){
            $total_time = 0;

            foreach($item as $ticket){
                $total_time += $ticket['estimatetime'];
            }
            $actors[$item[0]['actor_name']]['estimate'] = Mage::helper('codebase')->convertToHoursMins($total_time);
            $actors[$item[0]['actor_name']]['ticket'] = count($item);
            $overAllTime += $total_time;
            $developers = $developers + 1;
        }
        

        // For Special users who are not in US or Latin America office
        $since = date('Y-m-d h:i:s -0000', strtotime("-1 days") );
        $activities = Mage::getModel('codebase/codebase')->activity($query,$limit,$since); //product_shortcode,query
        $activities = array_reverse($activities);

        $allItems = array();
        foreach ($activities as $activity)
        {
            if($activity['type'] == 'ticketing_note')
            {
                $data = array();

                $data['actor_name'] = $activity['actor-name'];   
                if($activity['raw-properties']['changes']['estimated-time-string']) {
                    $time_format = explode(':',$activity['raw-properties']['changes']['estimated-time-string']['estimated-time-string'][1]);
                    if($time_format[1]) {
                        $time  = Mage::helper('codebase')->convertHoursToMinutes($activity['raw-properties']['changes']['estimated-time-string']['estimated-time-string'][1]);
                    } else {
                        $time = $activity['raw-properties']['changes']['estimated-time-string']['estimated-time-string'][1];
                    }

                    $data['estimatetime'] = $time;

                }
                if($data['estimatetime']){
                    if (in_array($activity['actor-email'], $utcUsers)) {
                        $allItems[$activity['actor-email']][] = $data;
                    } else {

                    }
                }
            }
        }
        foreach($allItems as $email => $item){
            $total_time = 0;

            foreach($item as $ticket){
                $total_time += $ticket['estimatetime'];
            }
            $actors[$item[0]['actor_name']]['estimate'] = Mage::helper('codebase')->convertToHoursMins($total_time);
            $actors[$item[0]['actor_name']]['ticket'] = count($item);
            $overAllTime += $total_time;
            $developers = $developers + 1;
        }


        $returnArray = array();
        $returnArray['hours_estimated'] = Mage::helper('codebase')->convertToHoursMins($overAllTime);
        $returnArray['dev_count'] = $developers;
        $returnArray['estimate_per_dev'] = Mage::helper('codebase')->convertToHoursMins($overAllTime/$developers);
        $returnArray['actors'] = $actors;

        //echo "<pre>";
        //print_r($returnArray);
        //exit;
        return $returnArray;

    }
}
