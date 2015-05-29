<?php
class Wage_Codebase_Model_Tickets extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codebase/tickets');
    }

    private function needToExclude($exclude_statuses, $status) {
        $statuses = explode(';', $exclude_statuses);
        foreach ($statuses as $s) {
            if (strstr(strtolower($status), $s) !== FALSE) {
                return true;
            }
        }
        return false;
    }

    /**
     * move tickets to backlog
     * @author atheotsky
     */
    public function fillBacklog()
    {
        if (!Mage::getStoreConfig('codebase/backlog/autofill')) return false;
        if (!Mage::getStoreConfig('codebase/backlog/backlog_user')) return false;
        /*prepare ignored assigneese*/
        $ignored_assigneese = array();
        foreach (explode(',', Mage::getStoreConfig('codebase/backlog/exclude_users')) as $user_id) {
            $ignored_assigneese[] = Mage::getModel('codebase/users')->findUser($user_id)->getUserName();
        }
        /*add users from non-selected companies to the ignored_assigneese array*/
        $accepted_companies = explode(',', Mage::getStoreConfig('codebase/backlog/company'));
        foreach (Mage::getModel('codebase/users')->getCollection() as $user) {
            $company = preg_replace('/[^a-zA-Z0-9\-]/', '', strtolower($user->getCompany()));
            if (!in_array($company, $accepted_companies)) $ignored_assigneese[] = $user->getUserName();
        }

        /*prepare ignored projects*/
        $ignored_projects = explode(',', Mage::getStoreConfig('codebase/backlog/exclude_projects'));

        $users = array();
        $collection = $this->getCollection();

        foreach ($collection as $row) {
            if (in_array($row->getAssignee(), $ignored_assigneese)) continue;
            if (in_array($row->getProjectId(), $ignored_projects)) continue;

            if (empty($users[$row->getAssignee()])) $users[$row->getAssignee()] = array();

            $users[$row->getAssignee()][strtotime($row->getUpdatedAt())] = array(
                "status" => $row->getStatusName(),
                "assignee" => $row->getAssignee(),
                "posturl" => "/{$row->getPermalink()}/tickets/{$row->getTicketId()}/notes",
            );
        }

        /*sort users' tickets array by time*/
        foreach ($users as $key=>$value) {
            krsort($users[$key]);
        }

        /*process tickets*/
        $statuses = strtolower(Mage::getStoreConfig('codebase/backlog/exclude_statuses'));
        foreach ($users as $name=>$tickets) {
            if (count($tickets) > intval(Mage::getStoreConfig('codebase/backlog/limit'))) {
                /*clear the list*/
                $counter = 0;
                foreach ($tickets as $key=>$ticket) {
                    /*check status*/
                    if ($this->needToExclude($statuses, $ticket['status'])) {
                        unset($tickets[$key]);
                        $counter++;
                    }
                }

                while($counter++ < intval(Mage::getStoreConfig('codebase/backlog/limit'))) array_shift($tickets);

                /*reload this user*/
                $users[$name] = $tickets;
            }
            else {
                /*remove this user from the list*/
                unset($users[$name]);
            }
        }

        /*do the job with filtered users list*/
        foreach ($users as $tickets) {
            foreach ($tickets as $ticket) {
                Mage::getModel('codebase/codebase')->assignTicket($ticket['posturl'], Mage::getStoreConfig('codebase/backlog/backlog_user'));
            }
        }
    }

    /**
     * close inactivity tickets
     * @author atheotsky
     */
    public function closeTickets()
    {
        $ttl = Mage::getStoreConfig('codebase/tickets/ttl');
        $excludeProjects = explode(',',Mage::getStoreConfig('codebase/exclude/exclude_projects'));
        if (empty($ttl)) return false;

        $lastupdate_limit = date("Y-m-d H:i:s", strtotime("-{$ttl} day"));
        $collection = $this->getCollection()
                    ->addFieldToFilter('updated_at', array('lt' => $lastupdate_limit))
                    ->addFieldToFilter('project_id', array('nin' => $excludeProjects))
                    ->addFieldToFilter('resolution','open');

        $status = Mage::getModel('codebase/statuses');
        $model = Mage::getModel('codebase/codebase');

        foreach ($collection as $ticket) {
            $params = array();
            //Removed below logic to stop ticket closing
            //$status = $status->findStatusbyLabel($ticket->getProjectId(), 'close');
            //$params['changes'] = array('status-id' => $status->getStatusId());
            $status = $status->findStatusbyLabel($ticket->getProjectId(), $ticket->getStatusName());
            if($status->getTreatAsClosed())
            {
                //Do nothing
            }
            else
            {
                if ($model->updateTicket($ticket, $params, Mage::getStoreConfig('codebase/tickets/close_comment'))){
    //                if (Mage::getStoreConfig('codebase/status/delete')) {
    //                    /*use the same settings from the mass action*/
    //                    $ticket->delete();
    //                }
    //                else {
    //                    $ticket->setStatusName($status->getName())->save();
    //                }
                }
            }
        }
    }

    public function updatePriorities(){
        //Logic for changing priority from Critical To High
        $ctl_to_high = Mage::getStoreConfig('codebase/priorities/ctl_to_high');
        $excludeProjects = explode(',',Mage::getStoreConfig('codebase/exclude/exclude_projects'));
        $comment = Mage::getStoreConfig('codebase/priorities/update_comment');
        if (!empty($ctl_to_high)){

            $lastupdate_limit = date("Y-m-d H:i:s", strtotime("-{$ctl_to_high} day"));
            $collection = $this->getCollection()
                            ->addFieldToFilter('updated_at', array('lt' => $lastupdate_limit))
                            ->addFieldToFilter('priority_name', "Critical")
                            ->addFieldToFilter('resolution', 'open')
                            ->addFieldToFilter('project_id', array('nin' => $excludeProjects));

            $priority = Mage::getModel('codebase/priorities');
            $model = Mage::getModel('codebase/codebase');

            foreach ($collection as $ticket) {
                $priority = $priority->findPrioritybyLabel($ticket->getProjectId(), 'High');
                $params['changes'] = array('priority-id' => $priority->getPriorityId());

                if ($result = $model->updateTicket($ticket, $params, $comment)){
                    if($result['id'])
                    {
                        $ticket->setPriorityName('High');
                        $ticket->setUpdatedAt(date("Y-m-d H:i:s"))->save();
                    }
                }
            }
        }

        //Logic for changing priority from High To Normal
        $high_to_norm = Mage::getStoreConfig('codebase/priorities/high_to_norm');
        $comment = Mage::getStoreConfig('codebase/priorities/update_comment');
        if (!empty($high_to_norm)){

            $lastupdate_limit = date("Y-m-d H:i:s", strtotime("-{$high_to_norm} day"));
            $collection = $this->getCollection()
                ->addFieldToFilter('updated_at', array('lt' => $lastupdate_limit))
                ->addFieldToFilter('priority_name', "High")
                ->addFieldToFilter('resolution', 'open')
                ->addFieldToFilter('project_id', array('nin' => $excludeProjects));

            $priority = Mage::getModel('codebase/priorities');
            $model = Mage::getModel('codebase/codebase');
            foreach ($collection as $ticket) {
                $priority = $priority->findPrioritybyLabel($ticket->getProjectId(), 'Normal');
                $params['changes'] = array('priority-id' => $priority->getPriorityId());
                //print_r($params);
                if ($result = $model->updateTicket($ticket, $params, $comment)){
                    if($result['id'])
                    {
                        $ticket->setPriorityName('Normal');
                        $ticket->setUpdatedAt(date("Y-m-d H:i:s"))->save();
                    }
                }
            }
        }

    }

    public function splitTickets()
    {
        $splitMinutes = Mage::getStoreConfig('codebase/splittickets/hours')*60;
        $comment = Mage::getStoreConfig('codebase/splittickets/message');
        $collection = $this->getCollection()
                        ->addFieldToFilter('time_left', array('gt' => $splitMinutes ))
                        ->addFieldToFilter('resolution','open');

        $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'))
                        ->where('company = "Wagento" ');
        $params = array();
        $model = Mage::getModel('codebase/codebase');
        foreach($collection as $ticket){
            $ticket = $this->load($ticket->getId());
            $result = $model->updateTicket($ticket, $params, $comment);
        }

    }
}
