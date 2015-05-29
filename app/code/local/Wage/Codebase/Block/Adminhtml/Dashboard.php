<?php
class Wage_Codebase_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Template {
 public function _construct()
    {   	
        parent::_construct();
        $this->setTemplate('codebase/index.phtml');
    }

    public function getUser()
    {
        $user = Mage::getSingleton('admin/session');
        return $user->getUser();
    }

    public function getMyTickets(){
        $user = $this->getUser();
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('assignee',$user->getApiUser())
            ->setOrder('priority_name','ASC');
        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();


        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        } else {
            if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
        }

        return $collection;
    }

    public function getLoggedTime()
    {
        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('updated_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ))
            ->addFieldToFilter('created_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ));
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        }
        $collection->getSelect()
            //->columns('SUM(time_added) as total_spent_time')
            ->columns('SUM(minutes) as total_logged_time');

        $data = $collection->getData();
        return Mage::helper('codebase')->convertToHoursMins($data[0]['total_logged_time']);
    }

    public function getClosedTickets()
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','close')
            ->addFieldToFilter('updated_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ));
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        }
        return count($collection->getData());
    }

    public function getNewTickets()
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('created_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ));
        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        } else{
            if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
        }
        return count($collection->getData());
    }

    public function getOpenTickets()
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
                        ->addFieldToFilter('resolution','open');

        $exclude = explode(';', Mage::getStoreConfig('codebase/tickets/billable_statuses'));
        foreach ($exclude as $status) {
            if (!empty($status)) $collection->addFieldToFilter('status_name', array('nlike' => "%{$status}%"));
        }

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        } else{
            if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
        }

        $totalTickets = count($collection->getData());
        $openTickets = '<p>'.$totalTickets.'</p>';

        $wagentoTickets = $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users',
            'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',
            array('company'))
            ->where(Mage::getConfig()->getTablePrefix()."codebase_users.company = 'Wagento'")
            ;
        $wagentoTickets = $collection->getSize();
        $openTickets .= '<p>'.$wagentoTickets.'</p>';

        $clientTickets = $totalTickets - $wagentoTickets;
        $openTickets .= '<p>'.$clientTickets.'</p>';

        return $openTickets;
    }

    public function getTotalCompletionTime()
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open');

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        } else{
            if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
        }

        $total = 0;
        foreach($collection as $ticket){
            if($ticket->getEstimatedTime() && ($ticket->getEstimatedTime() >= $ticket->getTotalTimeSpent()))
            {
                $total = $total + ($ticket->getEstimatedTime() - $ticket->getTotalTimeSpent());
            }
        }
        return Mage::helper('codebase')->convertToHoursMins($total);
    }

    public function getNotEstimatedTickets()
    {
        $excludeTypes = explode(',',Mage::getStoreConfig('codebase/report/exclude_category'));
        if(count($excludeTypes) == 1){
            $excludeTypes = array($excludeTypes);
        }

        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('estimated_time',0)
            ->addFieldToFilter('category_name', array('nin' => $excludeTypes))
            ->setOrder('project_name','ASC');

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        } else{
            if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
        }


        $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'))
            ->where('company = "Wagento" ');

        return count($collection->getData());
    }

    public function getTicketsCompletionDate()
    {
        /*$since = date('Y-m-d h:i:s -0600', strtotime("-7 days") );
        $developerBlock = new Wage_Codebase_Block_Adminhtml_Dailyreport();
        $developerReport = $developerBlock->getAllDeveloperReports($since);
        $developerCount = (int) $developerReport['dev_count'];*/

        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('updated_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ));
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        }
        $collection->getSelect()->group('user_id');    

        $developerCount = (int) count($collection->getData());

        $totalEstimationTime = (float) str_replace(":",".",$this->getTotalCompletionTime());

        $perDayHour = $developerCount * 1 * 6;

        $howManyDays = round(($totalEstimationTime) / $perDayHour);

        $nonBusinessDays = array('6','7');
        $startDate = date("Y-m-d");
        $holidays = array();
        $businessdate = Mage::getModel('codebase/businessdayscalculator')->addBusinessDays($startDate, $holidays, $nonBusinessDays, $howManyDays);
        
        return $businessdate;
    }

    public function getProjectId()
    {
        $pid = Mage::app()->getRequest()->getParam('pid');
        if($pid){
            return $pid;
        }
        return '';
    }

    public function getUserId()
    {
        $uid = Mage::app()->getRequest()->getParam('uid');    
        if($uid){
            return $uid;
        }

        return '';
    }

    public function getDeveloperLoggedTime()
    {
        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('updated_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ))
            ->addFieldToFilter('created_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ));
        if($this->getUserId()){
            $collection->addFieldToFilter('user_id',$this->getUserId());
        }
        $collection->getSelect()
            //->columns('SUM(time_added) as total_spent_time')
            ->columns('SUM(minutes) as total_logged_time');

        $data = $collection->getData();
        return Mage::helper('codebase')->convertToHoursMins($data[0]['total_logged_time']);
    }

    public function getDevelopersOpenTickets()
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

        if($this->getUserId()){    
            $collection->addFieldToFilter('codebase_users.user_id' , $this->getUserId());
        }

        return $collection->getSize();
    }

    public function getDeveloperTotalCompletionTime()
    {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open');

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
        
       $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users',
            'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',
            array('company'))
            ->where(Mage::getConfig()->getTablePrefix()."codebase_users.company = 'Wagento'")
            ;

        if($this->getUserId()){    
            $collection->addFieldToFilter('codebase_users.user_id' , $this->getUserId());
        }

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

    public function getDeveloperNotEstimatedTickets()
    {
        $excludeTypes = explode(',',Mage::getStoreConfig('codebase/report/exclude_category'));
        if(count($excludeTypes) == 1){
            $excludeTypes = array($excludeTypes);
        }

        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('estimated_time',0)
            ->addFieldToFilter('category_name', array('nin' => $excludeTypes))
            ->setOrder('project_name','ASC');

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));
        


        $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'))
            ->where('company = "Wagento" ');

        if($this->getUserId()){
            $collection->addFieldToFilter('codebase_users.user_id' , $this->getUserId());
        }            

        return count($collection->getData());
    }

    public function getDevelopersTicketsCompletionDate()
    {

        $collection = Mage::getModel('codebase/timetracking')->getCollection()
            ->addFieldToFilter('updated_at', array(
                'from'     => strtotime('-7 days', time()),
                'to'       => time(),
                'datetime' => true
            ));
        if($this->getProjectId()){
            $collection->addFieldToFilter('project_id',$this->getProjectId());
        }
        $collection->getSelect()->group('user_id');    

        $developerCount = (int) count($collection->getData());

        if($this->getUserId()){
            $developerCount = 1;
        } 

        $totalEstimationTime = (float) str_replace(":",".",$this->getDeveloperTotalCompletionTime());

        $perDayHour = $developerCount * 1 * 6;

        $howManyDays = round(($totalEstimationTime) / $perDayHour);

        $nonBusinessDays = array('6','7');
        $startDate = date("Y-m-d");
        $holidays = array();
        $businessdate = Mage::getModel('codebase/businessdayscalculator')->addBusinessDays($startDate, $holidays, $nonBusinessDays, $howManyDays);
        
        return $businessdate;
    }

}
