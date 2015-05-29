<?php
class Wage_Codebase_Block_Adminhtml_Dailyreport extends Mage_Adminhtml_Block_Template {
 public function _construct()
    {   	
        parent::_construct();
        $this->setTemplate('codebase/dailyreport.phtml');
    }

    public function getPeriod()
    {
        $period = Mage::app()->getRequest()->getParam('period');
        if($period){
            return $period;
        }
        return '';
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
    

    public function getDevelopersTimeTracking($period = NULL, $team = NULL) {

        $main_table = Mage::getSingleton('core/resource')->getTableName('codebase/timetracking');
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        

        switch ($period) {
        case 'monthly':
            $from = date('Y-m-d h:i:s', strtotime('-1 month'));
            $to = date('Y-m-d h:i:s', time());
            $workingdays = (int)Mage::getModel('codebase/businessdayscalculator')->workingdays($from, $to, array(), array(6, 7));
            $workingMinutes = $workingdays * 8 * 60;
            break;
        case 'weekly':
            $from = date('Y-m-d h:i:s', strtotime('-1 week'));
            $to = date('Y-m-d h:i:s', time());
            $workingdays = (int)Mage::getModel('codebase/businessdayscalculator')->workingdays($from, $to, array(), array(6, 7));
            $workingMinutes = $workingdays * 8 * 60;
            break;
        default:
            $from = date('Y-m-d h:i:s', strtotime('-1 days'));
            return $this->getAllDeveloperReports($from);
            break;
        }


        $UserTypeData = Mage::helper('codebase')->getAllUserType();

        $select = $adapter->select()->from(array('main_table' => $main_table), array());
        $select->columns(array('user_id', 'minutes', 'updated_at'));


        /*apply filters*/
        $select->where('main_table.updated_at >= ?', $from);
        $select->where('main_table.updated_at <= ?', $to);
        $select->where('main_table.user_id != ?', NULL);

        /*apply team filter*/
        $teams = $this->getUserTeam();
        if($this->getTeams()){
            $teamsUserId = array_keys($teams, $this->getTeams());
        }

        //echo $adapter->select();

        /*get projects and join to collect project names*/
        //$p = $adapter->select()->group('user_id');
        //$select->joinLeft(array('p' => $p), 'p.project_id = main_table.project_id', array('project' => 'project_name'));
        
        $period_sum = array(); //$project_sum = array();
        foreach ($adapter->fetchAll($select) as $row) {
            if($this->getTeams()){
                if(in_array($row['user_id'],$teamsUserId))
                    $user[$row['user_id']] += $row['minutes'];
            }else{
                $user[$row['user_id']] += $row['minutes'];
            }
        }

        foreach($user as $user_id => $minutes){
                      
            if(isset($UserTypeData[$user_id]))
            {            
                if($minutes >= $workingMinutes){
                    $additonal_time = abs($minutes - $workingMinutes);
                    $missed_hours = '+' . Mage::helper('codebase')->convertToHoursMins($additonal_time);
                    $overAllMissedTime -= $additonal_time;
                }else{ 
                    $missed_time = abs($minutes - $workingMinutes);
                    $missed_hours = '-' . Mage::helper('codebase')->convertToHoursMins($missed_time);
                    $overAllMissedTime += $missed_time;
                }


                $actors[$UserTypeData[$user_id]['name']]['user_type'] = $UserTypeData[$user_id]['type'];// ? $UserTypeData[$user_id]['type'] : 'Development - Backend';
                $actors[$UserTypeData[$user_id]['name']]['logged_hours'] = Mage::helper('codebase')->convertToHoursMins($minutes);
                $actors[$UserTypeData[$user_id]['name']]['missed_hours'] = $missed_hours;
                $actors[$UserTypeData[$user_id]['name']]['average'] = number_format( $minutes/$workingMinutes * 100, 2 ) . '%';
                $actors[$UserTypeData[$user_id]['name']]['teams'] = $teams[$user_id];
                $usertype['logged'][$UserTypeData[$user_id]['type']] += $minutes;
                $usertype['missed'][$UserTypeData[$user_id]['type']] += $missed_time;
                $usertype['count'][$UserTypeData[$user_id]['type']] += 1;
                $overAllTime += $minutes;
                
                $developers = $developers + 1;
            }        
        }

        $returnArray['hours_logged'] = Mage::helper('codebase')->convertToHoursMins($overAllTime);
        $returnArray['hours_missed'] = Mage::helper('codebase')->convertToHoursMins($overAllMissedTime);
        $returnArray['average'] = number_format( $overAllTime*100 /($workingMinutes*$developers), 2 ) . '%';
        $returnArray['dev_count'] = $developers;
        $returnArray['total_working_days'] = $workingdays;
        $returnArray['total_working_hours'] = Mage::helper('codebase')->convertToHoursMins($workingMinutes);
        $returnArray['hrs_per_dev'] = Mage::helper('codebase')->convertToHoursMins($overAllTime/$developers);
        $returnArray['actors'] = $actors;
        $returnArray['usertype'] = $usertype;

       return $returnArray;
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

        $UserTypeData = Mage::helper('codebase')->getAllUserType();

        /*apply team filter*/
        $teams = $this->getUserTeam();
        if($this->getTeams()){
            $teamsUserId = array_keys($teams, $this->getTeams());
        }

        $limit = 10000;
        //For US and Latin America Office


        $activities = Mage::getModel('codebase/codebase')->activity($query,$limit,$since); //product_shortcode,query
        $activities = array_reverse($activities);

        $allItems = array();
        foreach ($activities as $activity)
        {
            if($this->getTeams()){
                if(!in_array($activity['user-id'],$teamsUserId))
                    continue;
            }

            if($activity['type'] == 'add_time')
            {
                $changes = $activity['raw-properties']['changes'];
                $data = array();

                $data['user_id']                = $activity['user-id'];
                $data['subject']                = $activity['raw-properties']['summary'];
                $data['actor_name']             = $activity['actor-name'];
                $data['project_permalink']      = $activity['raw-properties']['project-permalink'];
                $data['project_name']           = $activity['raw-properties']['project-name'];
                $data['type']                   = $activity['type'];
                //$data['time-added']             = $this->convertToHoursMins($activity['raw-properties']['time-added']);
                if(!is_array($activity['raw-properties']['minutes'])) {
                    if($activity['raw-properties']['minutes']) {
                        $time_format = explode(':',$activity['raw-properties']['minutes']);
                        if($time_format[1]) {
                            // $input is valid HH:MM format.
                            $time             = $activity['raw-properties']['minutes'];

                        } else {
                            $time             = Mage::helper('codebase')->convertToHoursMins($activity['raw-properties']['minutes']);

                        }

                        $data['time_added'] =$time;
                        $data['time_in_minutes'] = $activity['raw-properties']['minutes'];
                    }
                }
                if($data['time_added']){
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
            $missed_time = 0;
            $additonal_time = 0;


            foreach($item as $ticket){
                $total_time += $ticket['time_in_minutes'];
            }

            

            if($total_time >= 480){
                $additonal_time = abs($total_time - 480);
                $missed_hours = '+' . Mage::helper('codebase')->convertToHoursMins($additonal_time);
                $overAllMissedTime -= $additonal_time;
            }else{ 
                $missed_time = abs($total_time - 480);
                $missed_hours = '-' . Mage::helper('codebase')->convertToHoursMins($missed_time);
                $overAllMissedTime += $missed_time;
            }
            
            $actors[$item[0]['actor_name']]['user_type'] = $UserTypeData[$item[0]['user_id']]['type'];// ? $UserTypeData[$item[0]['user_id']]['type'] : 'Development - Backend';
            $actors[$item[0]['actor_name']]['logged_hours'] = Mage::helper('codebase')->convertToHoursMins($total_time);
            $actors[$item[0]['actor_name']]['missed_hours'] = $missed_hours;
            $actors[$item[0]['actor_name']]['average'] = number_format( $total_time/480 * 100, 2 ) . '%';
            $actors[$item[0]['actor_name']]['teams'] = $teams[$item[0]['user_id']];
            $usertype['logged'][$actors[$item[0]['actor_name']]['user_type']] += $total_time;
            $usertype['missed'][$actors[$item[0]['actor_name']]['user_type']] += $missed_time;
            $usertype['count'][$actors[$item[0]['actor_name']]['user_type']] += 1;
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
            if($this->getTeams()){
                if(!in_array($activity['user-id'],$teamsUserId))
                    continue;
            }    

            if($activity['type'] == 'add_time')
            {
                $changes = $activity['raw-properties']['changes'];
                $data = array();

                $data['user_id']                = $activity['user-id'];
                $data['subject']                = $activity['raw-properties']['summary'];
                $data['actor_name']             = $activity['actor-name'];
                $data['project_permalink']      = $activity['raw-properties']['project-permalink'];
                $data['project_name']           = $activity['raw-properties']['project-name'];
                $data['type']                   = $activity['type'];
                //$data['time-added']             = $this->convertToHoursMins($activity['raw-properties']['time-added']);
                if(!is_array($activity['raw-properties']['minutes'])) {
                    if($activity['raw-properties']['minutes']) {
                        $time_format = explode(':',$activity['raw-properties']['minutes']);
                        if($time_format[1]) {
                            // $input is valid HH:MM format.
                            $time             = $activity['raw-properties']['minutes'];

                        } else {
                            $time             = Mage::helper('codebase')->convertToHoursMins($activity['raw-properties']['minutes']);

                        }

                        $data['time_added'] =$time;
                        $data['time_in_minutes'] = $activity['raw-properties']['minutes'];

                    }
                }

                if($data['time_added']){
                    if (in_array($activity['actor-email'], $utcUsers)) {
                        $allItems[$activity['actor-email']][] = $data;
                    } else {

                    }
                }
            }
        }

        foreach($allItems as $email => $item){
            $total_time = 0;
            $missed_time = 0;
            $additonal_time = 0;


            foreach($item as $ticket){
                $total_time += $ticket['time_in_minutes'];
            }

            

            if($total_time >= 480){
                $additonal_time = abs($total_time - 480);
                $missed_hours = '+' . Mage::helper('codebase')->convertToHoursMins($additonal_time);
                $overAllMissedTime -= $additonal_time;
            }else{ 
                $missed_time = abs($total_time - 480);
                $missed_hours = '-' . Mage::helper('codebase')->convertToHoursMins($missed_time);
                $overAllMissedTime += $missed_time;
            }
            
            $actors[$item[0]['actor_name']]['user_type'] = $UserTypeData[$item[0]['user_id']]['type'];// ? $UserTypeData[$item[0]['user_id']]['type'] : 'Development - Backend';
            $actors[$item[0]['actor_name']]['logged_hours'] = Mage::helper('codebase')->convertToHoursMins($total_time);
            $actors[$item[0]['actor_name']]['missed_hours'] = $missed_hours;
            $actors[$item[0]['actor_name']]['average'] = number_format( $total_time/480 * 100, 2 ) . '%';
            $actors[$item[0]['actor_name']]['teams'] = $teams[$item[0]['user_id']];
            $usertype['logged'][$actors[$item[0]['actor_name']]['user_type']] += $total_time;
            $usertype['missed'][$actors[$item[0]['actor_name']]['user_type']] += $missed_time;
            $usertype['count'][$actors[$item[0]['actor_name']]['user_type']] += 1;
            $overAllTime += $total_time;
            
            $developers = $developers + 1;

        }



        $returnArray = array();
        $returnArray['hours_logged'] = Mage::helper('codebase')->convertToHoursMins($overAllTime);
        $returnArray['hours_missed'] = Mage::helper('codebase')->convertToHoursMins($overAllMissedTime);
        $returnArray['average'] = number_format( $overAllTime*100 /(480*$developers), 2 ) . '%';
        $returnArray['total_working_days'] = 1;
        $returnArray['total_working_hours'] = Mage::helper('codebase')->convertToHoursMins(480);
        $returnArray['dev_count'] = $developers;
        $returnArray['hrs_per_dev'] = Mage::helper('codebase')->convertToHoursMins($overAllTime/$developers);
        $returnArray['actors'] = $actors;
        $returnArray['usertype'] = $usertype;
        /*
        echo "<pre>";
        print_r($returnArray);
        exit;*/
        return $returnArray;

    }
}
