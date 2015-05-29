<?php
class Wage_Codebase_Block_Adminhtml_Developerwork extends Mage_Adminhtml_Block_Template {
 public function _construct()
    {   	
        parent::_construct();
        $this->setTemplate('codebase/developerwork.phtml');
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
