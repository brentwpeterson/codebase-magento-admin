<?php
class Wage_Codebase_Block_Adminhtml_Tickets_Grid_Renderer_Timespent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $timespent = $row->getData($this->getColumn()->getIndex());
	    $permalink = $row->getData($this->getColumn()->getPermalink());
	    $val = $row->getData($this->getColumn()->getTicketId());
        $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$permalink.'/tickets/'.$val;
        $out = '';

        $out = $out.'<ul>';

        $activityCollection = Mage::getModel('codebase/activities')->getCollection()
                                //->addAttributeToSelect('number','actor_name','time_added','actor_email')
                                ->addFieldToFilter('number',$val)
                                ->addFieldToFilter('project_permalink',$permalink);
//        if($val == '101'){
//            echo "<pre>"; print_r($activityCollection->getData());exit;
//        }
        $timeArray = array();
        foreach($activityCollection as $activity)
        {
            if($activity['time_added'] != NULL){
                $time3 = ''; $time4= '';
                if ($timeArray[$activity['actor_email']]['time']) {

                    $time3 = $activity['time_added'];
                    $time4 = $timeArray[$activity['actor_email']]['time'];
                    $secs1 = strtotime($time3)-strtotime("00:00");
                    $timeArray[$activity['actor_email']]['time'] = date("H:i:s",strtotime($time4)+$secs1);
                } else {
                    $array = array();
                    $array['time'] = $activity['time_added'];
                    $array['name'] = $activity['actor_name'];
                    $timeArray[$activity['actor_email']] = $array;

                }
            }
        }


        foreach($timeArray as $item){
            $out = $out."<li>".$item['name'].' => '.date("H:i",strtotime($item['time'])).'</li>';
        }
        $out .= '</ul>';
        if(count($timeArray) > 0) {
            $out .= "<div>--------------------------------</div>";
            $out .= "<div>Total Time: ".$timespent."</div>";
        }
        return $out;
    }
}
