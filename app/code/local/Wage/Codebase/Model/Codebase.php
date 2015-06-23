<?php
class Wage_Codebase_Model_Codebase extends Wage_Codebase_Model_Abstract{
	
	public function getTickets()
    {
        $secure = 's'; // or leave null to use HTTP
        // OR: $c = new Codebase('username','password','hostname',$secure,'userpass'); // log in with normal credentials
       // $query = 'sort:priority resolution:open';

        $projects = $this->getProjects();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('codebase/refreshtime');
        $time = $readConnection->fetchCol('SELECT update_time FROM ' . $table . ' WHERE code = "ticket_refresh" ');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();
        $new_since = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        if(count($projects) > 0)
        {
            foreach ($projects as $project)
            {
                $project_name = $project['name'];
                $openTickets = array();
                if($project['status'] == 'active')
                {
                    $project_permalink = $project['permalink'];
                    $query = 'sort:priority resolution:open';
                    $updateDb = false;
                    $limit = 1000;
                    for($i=1;$i<=100;$i++)
                    {
                        //echo 'for -'.$i.'<br/>';
                        unset($tickets);
                        $tickets = $this->tickets($project_permalink,$query,$i); //product_shortcode,query

                        if(!is_array($tickets[0]) && count($tickets) > 0 )
                        {
                            $ticket = array();
                            $ticket = $tickets;
                            unset($tickets);
                            $tickets[0] = $ticket;
                        }
                        if(count($tickets))
                        {
                            $updateDb = true;
                            //echo $project_permalink.' '.$i.' '.count($tickets).'<br/>';
                            foreach ($tickets as $ticket)
                            {
                                unset($data);
                                unset($avail_ticket);

                                $avail_ticket = $this->loadTicketByNumber($ticket['ticket-id'],$ticket['project-id']);
                                if($avail_ticket->getId())
                                {
                                    $model = Mage::getModel('codebase/tickets')->load($avail_ticket->getId());
                                    $data = $model->getData();

                                } else {
                                    $model = Mage::getModel('codebase/tickets');
                                }
                                $openTickets[]              = $ticket['ticket-id'];
                                $data['ticket_id']          = $ticket['ticket-id'];
                                $data['summary']            = $ticket['summary'];
                                $data['ticket_type']        = $ticket['ticket-type'];
                                $data['project_name']       = $project_name;
                                $data['permalink']          = $project_permalink;
                                $data['assignee']           = $ticket['assignee'];
                                $data['reporter']           = $ticket['reporter'];
                                $data['category_name']      = $ticket['category']['name'];
                                $data['priority_name']      = $ticket['priority']['name'];
                                $data['status_name']        = $ticket['status']['name'];
                                $data['type_name']          = $ticket['type']['name'];
                                $data['resolution']         = 'open';
                                if(is_array($ticket['tags'])){
                                    $data['tags']               = implode(',',$ticket['tags']);
                                } else {
                                    $data['tags']               = $ticket['tags'];
                                }
                                $data['created_at']         = $ticket['created-at'];
                                $data['updated_at']         = $ticket['updated-at'];
                                $data['project_id']         = $ticket['project-id'];
                                if(!is_array($ticket['milestone-id'])) {
                                    $data['milestone_id']       = $ticket['milestone-id'];
                                }
                                if(is_array($ticket['milestone'])) {
                                    $data['milestone_name']         = $ticket['milestone']['name'];
                                }
                                if(is_array($ticket['estimated-time'])){
                                    $data['estimated_time']     = 0;
                                } else {
                                    $data['estimated_time']     = $ticket['estimated-time'];
                                }

                                $data['total_time_spent']   = $ticket['total-time-spent'];
                                if($data['estimated_time']  && $data['total_time_spent'])
                                {
                                    $data['time_left']   = $data['estimated_time'] - $data['total_time_spent'];
                                }

                                /* Count number of comments in tickets */
                                //if($data['comments']) {
                                    unset($ticketsNotes);
                                    $tnCount = 0;
                                    $lastStatusUpdaterUserId = 0;
                                    $ticketsNotes = $this->getTicketNotes($project_permalink, $ticket['ticket-id']);
                                    foreach($ticketsNotes['ticket-note'] as $tnKey => $tnValue){

                                        if(!empty($tnValue['content'])){
                                            $tnCount++;
                                        }
                                        if(strpos($tnValue['updates'], 'status_id') === false){

                                        } else {
                                            $lastStatusUpdaterUserId = $tnValue['user-id'];
                                        }
                                    }
                                    $data['comments']   = $tnCount;
                                //}
                                if($ticket['assignee-id'])
                                $data['assignee_id']           = $ticket['assignee-id'];

                                $data['last_status_updater_id'] = $lastStatusUpdaterUserId;


                                if($avail_ticket->getId()) {

                                    $model->setId($avail_ticket->getId());
                                    $model->setData($data);

                                } else {
                                    $model->setData($data);
                                }

                                try {
                                    $model->save();
                                } catch(Exception $e) {
                                    if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                                        Mage::log($e->getMessage(),null,"wage_codebase.log");
                                    }
                                }
                            }
                        }else {
                            break;
                        }
                    }
                }
                if($updateDb)
                {
                    $ticketsCollection = Mage::getModel('codebase/tickets')->getCollection()
                    ->addFieldToFilter('resolution','open')
                    ->addFieldToFilter('project_id',$project['project-id']);

                    foreach($ticketsCollection as $tkt){
                        if (in_array($tkt->getTicketId(), $openTickets)) {
                            // Do nothing
                        } else {
                            $ticket = $this->loadTicketByNumber($tkt->getTicketId(),$tkt->getProjectId());
                            $ticket->setResolution('close');
                            $ticket->save();
                        }
                    }
                }
            }
        }
        if(count($projects) > 0)
        {
            if($time[0]) {
                // Update Entry
                $__fields = array();
                $__fields['update_time'] = $new_since;
                $__where = $connection->quoteInto('code =?', 'ticket_refresh');
                $connection->update($table, $__fields, $__where);

            } else {
                // Insert Entry
                $fields = array();
                $fields['update_time'] = $new_since;
                $fields['code'] = 'ticket_refresh';
                $connection->insert($table, $fields);
            }
            $connection->commit();
        }
        //return $tickets;
    }

    function loadTicketByNumber($number,$projectId)
    {

        $obj = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('ticket_id',$number)
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();


        return $obj;
    }

    public function getProjects()
    {
        return $this->projects();
    }

    function loadProjectById($projectId)
    {

        $obj = Mage::getModel('codebase/projects')->getCollection()
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();
        return $obj;
    }

    public function importProjects()
    {
        $projects = $this->projects();
        $projectArray = array();
        if(count($projects) > 0)
        {
            foreach($projects as $proj){
                $loadedProject = $this->loadProjectById($proj['project-id']);
                $projectArray[] = $proj['project-id'];
                $data = array();
                if($loadedProject->getEntityId()) {
                    $project = Mage::getModel('codebase/projects')->load($loadedProject->getEntityId());
                    $data = $project->getData();
                } else {
                    $project = Mage::getModel('codebase/projects');
                }
                $data['project_name'] = $proj['name'];
                $data['project_id'] = $proj['project-id'];
                $data['permalink'] = $proj['permalink'];
                $data['status'] = $proj['status'];

                if($loadedProject->getEntityId()) {
                    $project->setEntityId($loadedProject->getEntityId());
                    $project->setId($project->getId());
                    $project->setData($data);
                } else {
                    $project->setData($data);
                }

                try {
                    $project->save();
                    $this->importProjectUsers($proj);
                } catch(Exception $e) {
                    if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                        Mage::log($e->getMessage(),null,"wage_codebase.log");
                    }
                }
            }

            $projectsCollection = Mage::getModel('codebase/projects')->getCollection();
            foreach($projectsCollection as $project){
                if (in_array($project->getProjectId(), $projectArray)) {
                    // Do Nothing
                } else {
                    $project->setStatus('deleted');
                    $project->save();
                }
            }
        }
    }

    function loadProjectUserById($projectId,$userId)
    {
        $obj = Mage::getModel('codebase/projectindex')->getCollection()
            ->addFieldToFilter('project_id',$projectId)
            ->addFieldToFilter('user_id',$userId)
            ->getFirstItem();

        return $obj;
    }

    public function importProjectUsers($proj)
    {
            $projectUsers = $this->projectusers($proj['permalink']);
            foreach($projectUsers as $user){

                $loadedProjectUser = $this->loadProjectUserById($proj['project-id'],$user['id']);

                $data = array();
                if($loadedProjectUser->getEntityId()) {

                    $projectUser = Mage::getModel('codebase/projectindex')->load($loadedProjectUser->getEntityId());
                    $data = $projectUser->getData();
                } else {

                    $projectUser = Mage::getModel('codebase/projectindex');
                }

                $data['project_id'] = $proj['project-id'];
                $data['user_id'] = $user['id'];
                $data['user_email'] = $user['email-address'];
                if($user['company'] != ''){
                    $data['company'] = $user['company'];
                }

                if($loadedProjectUser->getEntityId()) {
                    $projectUser->setEntityId($loadedProjectUser->getEntityId());
                    $projectUser->setId($projectUser->getId());
                    $projectUser->setData($data);
                } else {
                    $projectUser->setData($data);
                }
                try {
                    $projectUser->save();
                } catch(Exception $e) {
                    if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                        Mage::log($e->getMessage(),null,"wage_codebase.log");
                    }
                }
            }
    }

    public function createUsers()
    {
        $users = $this->users();
        $wagentoStaff = 0;

        foreach($users as $user)
        {
            $admin = $this->__userExists($user['email-address']);
            if(!$admin)
            {
                if($user['enabled'])
                {
                    try
                    {
                        if($user['company'] == 'Wagento')
                        {
                            $wagentoStaff = 1;
                        }
                        $adminuser = Mage::getModel("admin/user")
                            ->setUsername($user['username'])
                            ->setFirstname($user['first-name'])
                            ->setLastname($user['last-name'])
                            ->setEmail($user['email-address'])
                            ->setPassword($user['first-name'].'@@123')
                            ->setApiUser($user['username'])
                            ->setApiKey($user['api-key'])
                            ->setWagentoStaff($wagentoStaff)
                            ->save();

                        /*$role = Mage::getModel("admin/role");
                        $role->setParent_id(1);
                        $role->setTree_level(1);
                        $role->setRole_type('U');
                        $role->setUser_id($adminuser->getId());
                        $role->save();
                        */

                    }
                    catch (Exception $e) {
                        Mage::log($e->getMessage(),null,"wage_codebase.log");
                    }
                }
            }
            else{
                if(!$user['enabled'])
                {
                    $admin->setIsActive(0);//or 0
                    $admin->save();
                } else {
                    $wagentoStaff = 0;
                    if($user['company'] == 'Wagento')
                    {
                        $wagentoStaff = 1;
                    }
                    $admin->setFirstname($user['first-name'])
                        ->setLastname($user['last-name'])
                        ->setApiUser($user['username'])
                        ->setApiKey($user['api-key'])
                        ->setWagentoStaff($wagentoStaff)
                        ->save();
                }
            }
        }
    }

    function __userExists($email, $websiteId = null)
    {
        $admin = Mage::getModel("admin/user")->getCollection()
            ->addFieldToFilter('email',$email)
            ->getFirstItem();

        if ($admin->getId()) {
            return $admin;
        }
        return false;
    }

    public function getUsers()
    {
        return $this->users();
    }

    public function getActivity()
    {
        $isFirstTime = false;
        $query = 'sort:priority status:open';
        $limit = 10000;
        $updatedProjectIds = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('codebase/time');
        $time = $readConnection->fetchCol('SELECT last_activity_time FROM ' . $table);
        $activitiesAvailable = false;
        if($time[0]) {
            $since = $time[0].' -0600';
        } else {
            //$since = '2014-11-01 15:30:06';
            $isFirstTime = true;
            $since = date('Y-m-d H:i:s', strtotime("-30 days") );
            $since = $since.' -0600';
        }
        //$since = '2015-01-06 09:41:55';
        $projects = $this->getProjects();
        if(count($projects) > 0)
        {
            foreach ($projects as $project)
            {
                if($project['status'] == 'active')
                {
                    $project_permalink = $project['permalink'];
                    $activities = $this->activity($query,$limit,$since,$project_permalink); //product_shortcode,query
                    foreach ($activities as $activity)
                    {
                        if($activity['type'] == 'ticketing_note' || $activity['type'] == 'ticketing_ticket')
                        {
                            $activitiesAvailable = true;
                            $changes = $activity['raw-properties']['changes'];
                            $data = array();

                            //$model = $this->loadTicketByNumber($activity['raw-properties']['number'],$activity['project-id']);
                            //$model = $this->loadTicketByNumber($activity['raw-properties']['number'],$activity['project-id']);
                            $model = Mage::getModel('codebase/activities');

                            $data['number']                 = $activity['raw-properties']['number'];
                            $data['subject']                = $activity['raw-properties']['subject'];
                            if(!is_array($activity['raw-properties']['content'])) {
                            $data['content']                = $activity['raw-properties']['content'];
                            }
                            $data['actor_email']            = $activity['actor-email'];
                            $data['actor_name']             = $activity['actor-name'];
                            $data['project_id']             = $activity['project-id'];
                            $data['timestamp']              = $activity['timestamp'];
                            if(!$isFirstTime){
                                $updatedProjectIds[]            = $activity['project-id'];
                            }
                            $data['priority']               = $activity['raw-properties']['priority'];
                            $data['project_permalink']      = $activity['raw-properties']['project-permalink'];
                            $data['project_name']           = $activity['raw-properties']['project-name'];
                            //$data['time-added']             = $this->convertToHoursMins($activity['raw-properties']['time-added']);
                            if(!is_array($activity['raw-properties']['time-added'])) {
                                if($activity['raw-properties']['time-added']) {
                                        $time_format = explode(':',$activity['raw-properties']['time-added']);
                                        if($time_format[1]) {
                                            // $input is valid HH:MM format.
                                            $time             = $activity['raw-properties']['time-added'];

                                        } else {
                                            $time             = $this->convertToHoursMins($activity['raw-properties']['time-added']);

                                        }

                                            $data['time_added'] =$time;

                                    }
                            }

                            if(is_array($changes['status-id']['status-id'])) {
                                $data['status']             = $changes['status-id']['status-id'][1];
                            }
                            if(is_array($changes['assignee-id']['assignee-id'])) {
                                $data['assignee']          = $changes['assignee-id']['assignee-id'][1];
                            }
                            if(is_array($changes['estimated-time-string']['estimated-time-string'])) {

                                if($changes['estimated-time-string']['estimated-time-string'][1])
                                {
                                    $time_format1 = explode(':',$changes['estimated-time-string']['estimated-time-string'][1]);

                                    if($time_format1[1]) {
                                        // $input is valid HH:MM format.
                                        $data['estimated_time']    = $changes['estimated-time-string']['estimated-time-string'][1];

                                    } else {
                                        $data['estimated_time']    = $this->convertToHoursMins($changes['estimated-time-string']['estimated-time-string'][1]);

                                    }
                                    if(is_array($changes['estimated-time-string']['estimated-time-string'][0])) {
                                        $ticket = $this->loadTicketByNumber($activity['raw-properties']['number'],$activity['project-id']);
                                        $ticket = Mage::getModel('codebase/tickets')->load($ticket->getId());
                                        $ticket->setOrigEstimatedTime($this->hoursToMinutes($data['estimated_time']));
                                        $ticket->save();

                                    }
                                }
                            }

                            $data['time_left'] = 0;

                            $model->setData($data);
                            try {
                                $model->save();
                            } catch(Exception $e) {
                                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                                }
                            }
                        }
                    }
                }
            }
        }
        $time = $readConnection->fetchCol('SELECT last_activity_time FROM ' . $table);
        if($activitiesAvailable)
        {
            if($time[0]) {
                $since = $time[0].' -0600';
                $new_since = date("Y-m-d H:i:s");
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

                $connection->beginTransaction();

                $__fields = array();
                $__fields['last_activity_time'] = $new_since;
                $__where = $connection->quoteInto('id =?', '1');
                $connection->update($table, $__fields, $__where);

                $connection->commit();

            } else {
                //$since = '2014-11-01 15:30:06';
                $isFirstTime = true;
                $since = date('Y-m-d H:i:s', strtotime("-30 days") );
                $new_since = date("Y-m-d H:i:s");
                $connection = Mage::getSingleton('core/resource')
                    ->getConnection('core_write');
                $connection->beginTransaction();
                $fields = array();
                $fields['last_activity_time'] = $new_since;
                $connection->insert($table, $fields);
                $connection->commit();
                $since = $since.' -0600';

            }
            $updatedProjectIds = array_unique($updatedProjectIds);
            foreach($updatedProjectIds as $projectId)
            {
                $project = Mage::getModel('codebase/projects')->loadProjectByProjectId($projectId);
                $project->setLastUpdatedAt($new_since);
                $project->save();
            }
        }
        /*
        $coll = Mage::getModel('codebase/tickets')->getCollection();
        foreach($coll as $item)
        {
            if($item->getEstimatedTime() && $item->getTimeAdded())
            {
                $time3 = $item->getEstimatedTime();
                $time4 = $item->getTimeAdded();
                $secs1 = strtotime($time4)-strtotime("00:00");
                $result1 = date("H:i:s",strtotime($time3)-$secs1);
                $item->setTimeLeft($result1);
                $item->save();
            }
        }
        */
    }

    function convertToHoursMins($time, $format = '%d:%d') {
        settype($time, 'integer');
        if ($time < 1) {
            return ;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }

    function hoursToMinutes($hours)
    {
        if (strstr($hours, ':'))
        {
            # Split hours and minutes.
            $separatedData = split(':', $hours);

            $minutesInHours    = $separatedData[0] * 60;
            $minutesInDecimals = $separatedData[1];

            $totalMinutes = $minutesInHours + $minutesInDecimals;
        }
        else
        {
            $totalMinutes = $hours * 60;
        }

        return $totalMinutes;
    }
    /**
     * update ticket status
     * @author atheotsky
     */
    public function updateTicket($ticket, $params, $message = null) {
        $xml = '<ticket-note>';

        /*add support for other message when updating tickets*/
        if (empty($message)) {
            $xml .= '<content><![CDATA['.Mage::getStoreConfig('codebase/status/append_comment').']]></content>';
        }
        else {
            $xml .= '<content><![CDATA['.$message.']]></content>';
        }

        if($params['minutes'] != null) {
            $xml .= '<time-added><![CDATA['.intval($params['minutes']).']]></time-added>';
        }
        if(!empty($params['changes'])) {
            $xml .= '<changes>';
            foreach($params['changes'] as $key=>$value) {
                $xml .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
            }
            $xml .= '</changes>';
        }
        $xml .= '<private>1</private>';
        $xml .= '</ticket-note>';
        $result = $this->post('/' . $ticket->getPermalink() .'/tickets/' . $ticket->getTicketId() . '/notes', $xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));

        return $result;
    }

    
    /**
     * collect project statuses
     * @author atheotsky
     */
    public function getStatuses()
    {
        $statuses = array();
        $projects = $this->getProjects();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $isStatusFound = false;
        foreach ($projects as $project) {
            $statuses = $this->statuses($project['permalink']);
            foreach ($statuses as $status) {
                $isStatusFound = true;
                $model = Mage::getModel('codebase/statuses')->findStatus($project['project-id'], $status['id']);
                if ($model->getId()) {
                    $model->setName($status['name'])
                        ->setBackgroundColor($status['color'])
                        ->setOrder($status['order'])
                        ->setTreatAsClosed($status['treat-as-closed'] == 'true');
                }
                else {
                    $model->setStatusId($status['id'])
                        ->setProjectId($project['project-id'])
                        ->setName($status['name'])
                        ->setBackgroundColor($status['color'])
                        ->setOrder($status['order'])
                        ->setTreatAsClosed($status['treat-as-closed'] == 'true');
                }

                try {
                    $model->save();
                }
                catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        if($isStatusFound)
        {
            $new_since = Mage::getModel('core/date')->date('Y-m-d H:i:s');

            $write->beginTransaction();
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $table = Mage::getSingleton('core/resource')->getTableName('codebase/refreshtime');
            $time = $read->fetchCol('SELECT update_time FROM ' . $table . ' WHERE code = "status_refresh" ');
            if($time[0]) {
                $fields = array();
                $fields['update_time'] = $new_since;
                $where = $write->quoteInto('code =?', 'status_refresh');
                $write->update($table, $fields, $where);

            } else {
                $fields = array();
                $fields['update_time'] = $new_since;
                $fields['code'] = 'status_refresh';
                $write->insert($table, $fields);
            }
            $write->commit();
        }
    }

    public function sendReports()
    {
        $html = "";
        $projectName = '';
        $excludeTypes = explode(',',Mage::getStoreConfig('codebase/report/exclude_category'));
        if(count($excludeTypes) == 1){
            $excludeTypes = array($excludeTypes);
        }
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('estimated_time',0)
            ->addFieldToFilter('category_name', array('nin' => $excludeTypes))
            ->setOrder('project_name','ASC');

        $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'))
            ->where('company = "Wagento" ');


        foreach($collection as $item){
            if(($projectName == '')|| ($item['project_name'] != $projectName)){
                $projectName = $item['project_name'];
                $projectOwner = Mage::getModel('codebase/projects')->loadProjectByProjectId($item->getProjectId())->getUserId();
                if($projectOwner) {
                    $projectOwner = Mage::getModel('codebase/users')->findUser($projectOwner);
                    $html .= '<h3>'.$projectName.' - '.$projectOwner->getFirstName().' '.$projectOwner->getLastName().'</h3>';
                } else {
                    $html .= '<h3>'.$projectName.' - Needs Product Owner Assignment</h3>';

                }
            }
            $project_permalink = $item['permalink'];
            $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$project_permalink.'/tickets/'.$item['ticket_id'];
            $html .= '<a target="_blank" href="'.$url.'">'.$project_permalink.'-'.$item['ticket_id'].'</a><br/>';
        }
        $email = explode(',',Mage::getStoreConfig('codebase/report/recipient'));
        $body = $html;
        $mail = Mage::getModel('core/email');
        $mail->setToName('Tickets');
        $mail->setToEmail($email);
        $mail->setBody($body);
        $mail->setSubject('Tickets - Without Estimation');
        $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
        $mail->setFromName("Open Tickets");
        $mail->setType('html');// You can use 'html' or 'text'

        try {
            $mail->send();
        }
        catch (Exception $e) {
            if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                Mage::log($e->getMessage(),null,"wage_codebase.log");
            }

        }
    }

    public function sendOverEstimateReports()
    {
        $html = "";
        $projectName = '';

        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('total_time_spent', array('neq' => 0))
            ->addFieldToFilter('estimated_time', array('neq' => 0))
            ->addFieldToFilter('total_time_spent', array ('gt' => new Zend_Db_Expr('estimated_time') ) )
            ->setOrder('project_name','ASC');

        $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'))
            ->where('company = "Wagento" ');


        foreach($collection as $item){
            if(($projectName == '')|| ($item['project_name'] != $projectName)){
                $projectName = $item['project_name'];
                $html .= '<h3>'.$projectName.'</h3>';
            }
            $project_permalink = $item['permalink'];
            $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$project_permalink.'/tickets/'.$item['ticket_id'];
            $html .= '<a target="_blank" href="'.$url.'">'.$project_permalink.'-'.$item['ticket_id'].'</a><br/>';
        }
        $email = explode(',',Mage::getStoreConfig('codebase/report/over_recipient'));
        $body = $html;
        $mail = Mage::getModel('core/email');
        $mail->setToName('Tickets');
        $mail->setToEmail($email);
        $mail->setBody($body);
        $mail->setSubject('Tickets - Over Estimation');
        $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
        $mail->setFromName("Open Tickets");
        $mail->setType('html');// You can use 'html' or 'text'

        try {
            $mail->send();
        }
        catch (Exception $e) {
            if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                Mage::log($e->getMessage(),null,"wage_codebase.log");
            }

        }

    }

    /**
     * update codebase users table 
     */
    public function fetchUsers() {
        $users = $this->users();

        foreach ($users as $user) {
            if (empty($user['id'])) continue;

            $model = Mage::getModel('codebase/users')->findUser($user['id']);

            if (is_array($user['company'])) $user['company'] = implode('-', array_values($user['company']));
            if (is_array($user['time-zone'])) $user['time-zone'] = implode('-', array_values($user['time-zone']));
            if (is_array($user['username'])) $user['username'] = implode('-', array_values($user['username']));
            if($user['company'] == "Array")
            {
                $user['company'] = '';
	        } 
            $model->setCompany($user['company'])
                ->setApiKey($user['api-key'])
                ->setEmailAddress($user['email-address'])
                ->setUserId($user['id'])
                ->setFirstName($user['first-name'])
                ->setLastName($user['last-name'])
                ->setUserName($user['username'])
                ->setTimeZone(strval($user['time-zone']))
                ->setEnabled($user['enabled'] == 'true');

            try {
                $model->save();
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * assign Ticket to $user_id
     */
    public function assignTicket($url, $user_id) {
        $xml = '<ticket-note>';
        $xml .= '<changes>';
        $xml .= '<assignee-id>'.$user_id.'</assignee-id>';
        $xml .= '</changes>';
        $xml .= '<private>1</private>';
        $xml .= '</ticket-note>';

        $result = $this->post($url, $xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));

        return $result;
    }

    /**
     * prepare data for the module to work
     * @author atheotsky
     */
    public function initializeCodebaseData() {
        $this->getTickets();
        $this->importProjects();
        $this->fetchUsers();
        $this->getStatuses();
        $this->getPriorities();
        $this->getActivity();
        $this->getMilestones();
    }

    public function sendDeveloperReport(){
        $query = 'sort:priority status:open';
        $utcUsers = array();
        $utcUsers = explode(',', Mage::getStoreConfig('codebase/report/utc_users'));
        $limit = 10000;
        //For US and Latin America Office

        $since = date('Y-m-d 00:00:00 -0600', strtotime("-1 days") );

        $activities = $this->activity($query,$limit,$since); //product_shortcode,query
        $activities = array_reverse($activities);

        $allItems = array();
        foreach ($activities as $activity)
        {
            if($activity['type'] == 'add_time')
            {
                 $changes = $activity['raw-properties']['changes'];
                $data = array();


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
                            $time             = $this->convertToHoursMins($activity['raw-properties']['minutes']);

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
            $html = '';
            $html .= "<h3>Hello, ".$item[0]['actor_name']."</h3>";
            $html .= "<p>You have worked on following tickets yesterday,</p>";
            $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;">
                         <tr>
                          <th> Project  </th>
                          <th> Summary </th>
                          <th> Time Added </th>
                        </tr>';


            foreach($item as $ticket){
                $html .= '<td>'.$ticket['project_name'].'</td>';
                $html .= '<td align="center">'.$ticket['subject'].'</td>';
                $html .= '<td align="center">'.$ticket['time_added'].'</td></tr>';
                $total_time += $ticket['time_in_minutes'];
            }
            $html .= '<tr><td style="border-top: 2px dashed #000">&nbsp;</td>';
            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>Total Logged Time</b></td>';
            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>'.$this->convertToHoursMins($total_time).'</b></td></tr>';
            $html .= '</table><br/>NOTE:This is autogenerated email so please do not reply to this email';

            $body = $html;
            $mail = Mage::getModel('core/email');
            $mail->setToName($item[0]['actor_name']);
            $mail->setToEmail($email);
            $mail->setBody($body);
            $mail->setSubject('Daily Report');
            $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
            $mail->setFromName("Daily Report");
            $mail->setType('html');// You can use 'html' or 'text'

            try {
                $mail->send();
            }
            catch (Exception $e) {
                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                }

            }
        }

        // For Special users who are not in US or Latin America office
        $since = date('Y-m-d 00:00:00 -0000', strtotime("-1 days") );
        $activities = $this->activity($query,$limit,$since); //product_shortcode,query
        $activities = array_reverse($activities);

        $allItems = array();
        foreach ($activities as $activity)
        {
            //if($activity['type'] == 'ticketing_note' || $activity['type'] == 'ticketing_ticket')
            if($activity['type'] == 'add_time')
            {
                $changes = $activity['raw-properties']['changes'];
                $data = array();


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
                            $time             = $this->convertToHoursMins($activity['raw-properties']['minutes']);

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
            $html = '';
            $html .= "<h3>Hello, ".$item[0]['actor_name']."</h3>";
            $html .= "<p>You have worked on following tickets yesterday,</p>";
            $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;">
                        <tr>
                          <th> Project  </th>
                          <th> Summary </th>
                          <th> Time Added </th>
                        </tr>';

            foreach($item as $ticket){
                //$url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$ticket['project_permalink'].'/tickets/'.$ticket['number'];
                //$html .= '<tr><td align="center"><a target="_blank" href="'.$url.'">'.$ticket['project_name'].'-'.$ticket['number'].'</a><br/></td>';
                //$html .= '<td>'.$ticket['subject'].'</td>';
                $html .= '<tr><td>'.$ticket['project_name'].'</td>';
                $html .= '<td align="center">'.$ticket['subject'].'</td>';
                $html .= '<td align="center">'.$ticket['time_added'].'</td></tr>';
                $total_time += $ticket['time_in_minutes'];
            }
            $html .= '<tr><td style="border-top: 2px dashed #000">&nbsp;</td>';
            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>Total Logged Time</b></td>';
            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>'.$this->convertToHoursMins($total_time).'</b></td></tr>';
            $html .= '</table><br/>NOTE:This is autogenerated email so please do not reply to this email';
            $body = $html;
            $mail = Mage::getModel('core/email');
            $mail->setToName($item[0]['actor_name']);
            $mail->setToEmail($email);
            $mail->setBody($body);
            $mail->setSubject('Daily Report');
            $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
            $mail->setFromName("Daily Report");
            $mail->setType('html');// You can use 'html' or 'text'

            try {
                $mail->send();
            }
            catch (Exception $e) {
                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                }

            }
        }

    }

    /**
     * cron job to get/update timetracking
     * @author atheotksy
     */
    public function getTimetracking()
    {
        $select = Mage::getModel('codebase/tickets')->getCollection()->getSelect()->group('permalink');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');


        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $new_since = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        $write->beginTransaction();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('codebase/refreshtime');
        $time = $read->fetchCol('SELECT update_time FROM ' . $table . ' WHERE code = "timetracking_refresh" ');
        if($time[0]) {
            $fields = array();
            $fields['update_time'] = $new_since;
            $where = $write->quoteInto('code =?', 'timetracking_refresh');
            $write->update($table, $fields, $where);

        } else {
            $fields = array();
            $fields['update_time'] = $new_since;
            $fields['code'] = 'timetracking_refresh';
            $write->insert($table, $fields);
        }
        $write->commit();

        /*fast way to collect all active projects base on open tickets*/
        $projects = array();
        foreach ($read->fetchAll($select) as $ticket) {
            if (empty($ticket['permalink'])) continue;
            $projects[] = array(
                'permalink' => $ticket['permalink'],
                'project-id' => $ticket['project_id']
            );
        }
        /*$now = Mage::getModel('core/date')->date('Y-m-d H:i:s');*/

        foreach ($projects as $project) {
            $period = Mage::getModel('codebase/timetracking')->identifyPeriod($project['project-id']);

            $timetracking = $this->timetracking($project['permalink'], $period);
            foreach ($timetracking as $record) {
                $model = Mage::getModel('codebase/timetracking')->loadByTrackingId($record['id']);
                if ($model->getId()) {
                    $model->setSummary($record['summary'])
                        ->setMinutes($record['minutes'])
                        ->setSessionDate($record['session-date'])
                        ->setUserId($record['user-id'])
                        ->setUpdatedAt($record['updated-at']);
                }
                else {
                    $model->setTrackingId($record['id'])
                        ->setSummary($record['summary'])
                        ->setMinutes($record['minutes'])
                        ->setSessionDate($record['session-date'])
                        ->setUserId($record['user-id'])
                        ->setProjectId($project['project-id'])
                        ->setTicketId($record['ticket-id'])
                        ->setCreatedAt($record['created-at'])
                        ->setUpdatedAt($record['updated-at']);
                }

                try {
                    $model->save();
                }
                catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
    }

    public function getPriorities()
    {
        $priorities = array();
        $projects = $this->getProjects();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $new_since = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        $write->beginTransaction();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('codebase/refreshtime');
        $time = $read->fetchCol('SELECT update_time FROM ' . $table . ' WHERE code = "priority_refresh" ');
        if($time[0]) {
            $fields = array();
            $fields['update_time'] = $new_since;
            $where = $write->quoteInto('code =?', 'priority_refresh');
            $write->update($table, $fields, $where);

        } else {
            $fields = array();
            $fields['update_time'] = $new_since;
            $fields['code'] = 'priority_refresh';
            $write->insert($table, $fields);
        }
        $write->commit();

        foreach ($projects as $project) {
            if($project['status'] == 'active') {
                $priorities = $this->priorities($project['permalink']);

                foreach ($priorities as $priority) {
                    $model = Mage::getModel('codebase/priorities')->findPriority($project['project-id'], $priority['id']);
                    if ($model->getId()) {
                        $model->setName($priority['name'])
                            ->setColor($priority['colour'])
                            ->setDefault($priority['default'])
                            ->setPosition($priority['position'] == 'true');
                    }
                    else {
                        $model->setPriorityId($priority['id'])
                            ->setProjectId($project['project-id'])
                            ->setName($priority['name'])
                            ->setColor($priority['colour'])
                            ->setDefault($priority['default'])
                            ->setPosition($priority['position'] == 'true');
                    }

                    try {
                        $model->save();
                    }
                    catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }

    public function getMilestones()
    {
        $projects = $this->getProjects();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $new_since = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        $write->beginTransaction();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('codebase/refreshtime');
        $time = $read->fetchCol('SELECT update_time FROM ' . $table . ' WHERE code = "milestone_refresh" ');
        if($time[0]) {
            $fields = array();
            $fields['update_time'] = $new_since;
            $where = $write->quoteInto('code =?', 'milestone_refresh');
            $write->update($table, $fields, $where);

        } else {
            $fields = array();
            $fields['update_time'] = $new_since;
            $fields['code'] = 'milestone_refresh';
            $write->insert($table, $fields);
        }
        $write->commit();

        foreach ($projects as $project) {
            $project_name = $project['name'];
            if($project['status'] == 'active') {
                $milestones = array();
                $milestones = $this->milestones($project['permalink']);
                if(array_key_exists(0,$milestones))
                {
                    foreach ($milestones as $milestone) {
                        $model = Mage::getModel('codebase/milestones')->findMilestone($project['project-id'], $milestone['id']);
                        if ($model->getId()) {
                            $model->setName($milestone['name'])
                                ->setStatus($milestone['status'])
                                ->setEstimatedTime($milestone['estimated-time']);
                            if(!is_array($milestone['responsible-user-id'])){
                                $model->setResponsibleUserId($milestone['responsible-user-id']);
                            }

                            if(!is_array($milestone['start-at'])){
                                $model->setStartAt($milestone['start-at']);
                            }
                            if(!is_array($milestone['deadline'])){
                                $model->setDeadline($milestone['deadline']);
                            }
                            if(!is_array($milestone['parent-id'])){
                                $model->setParentId($milestone['parent-id']);
                            }
                            if(!is_array($milestone['description'])){
                                $model->setDescription($milestone['description']);
                            }
                        }
                        else {
                            $model->setMilestoneId($milestone['id'])
                                ->setProjectId($project['project-id'])
                                ->setProjectName($project_name)
                                ->setName($milestone['name'])
                                ->setStatus($milestone['status'])
                                ->setEstimatedTime($milestone['estimated-time']);

                            if(!is_array($milestone['responsible-user-id'])){
                                $model->setResponsibleUserId($milestone['responsible-user-id']);
                            }

                            if(!is_array($milestone['start-at'])){
                                $model->setStartAt($milestone['start-at']);
                            }
                            if(!is_array($milestone['deadline'])){
                                $model->setDeadline($milestone['deadline']);
                            }
                            if(!is_array($milestone['parent-id'])){
                                $model->setParentId($milestone['parent-id']);
                            }
                            if(!is_array($milestone['description'])){
                                $model->setDescription($milestone['description']);
                            }
                        }

                        try {
                            $model->save();
                        }
                        catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                } else {
                    $milestone = $milestones;
                    $model = Mage::getModel('codebase/milestones')->findMilestone($project['project-id'], $milestone['id']);
                    if ($model->getId()) {
                        $model->setName($milestone['name'])
                            ->setStatus($milestone['status'])
                            ->setEstimatedTime($milestone['estimated-time']);
                        if(!is_array($milestone['responsible-user-id'])){
                            $model->setResponsibleUserId($milestone['responsible-user-id']);
                        }

                        if(!is_array($milestone['start-at'])){
                            $model->setStartAt($milestone['start-at']);
                        }
                        if(!is_array($milestone['deadline'])){
                            $model->setDeadline($milestone['deadline']);
                        }
                        if(!is_array($milestone['parent-id'])){
                            $model->setParentId($milestone['parent-id']);
                        }
                        if(!is_array($milestone['description'])){
                            $model->setDescription($milestone['description']);
                        }
                    }
                    else {
                        $model->setMilestoneId($milestone['id'])
                            ->setProjectId($project['project-id'])
                            ->setProjectName($project_name)
                            ->setName($milestone['name'])
                            ->setStatus($milestone['status'])
                            ->setEstimatedTime($milestone['estimated-time']);

                        if(!is_array($milestone['responsible-user-id'])){
                            $model->setResponsibleUserId($milestone['responsible-user-id']);
                        }

                        if(!is_array($milestone['start-at'])){
                            $model->setStartAt($milestone['start-at']);
                        }
                        if(!is_array($milestone['deadline'])){
                            $model->setDeadline($milestone['deadline']);
                        }
                        if(!is_array($milestone['parent-id'])){
                            $model->setParentId($milestone['parent-id']);
                        }
                        if(!is_array($milestone['description'])){
                            $model->setDescription($milestone['description']);
                        }
                    }

                    try {
                        $model->save();
                    }
                    catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }

    public function changeProductStatus($projectId,$status) {
        $xml = '<project>';
        $xml .= '<status>'.$status.'</status>';
        $xml .= '</project>';
        $result = $this->put('/project/' . $projectId, $xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
        return $result;
    }

    public function sendProjectReport($userId,$clientId,$project_permalink,$noteTitle='',$noteSubject=''){
        $productOwner = Mage::getModel('codebase/users')->getCollection()
                        ->addFieldToFilter('user_id',$userId)
                        ->getFirstItem();


        $client = Mage::getModel('codebase/users')->getCollection()
                        ->addFieldToFilter('user_id',$clientId)
                        ->getFirstItem();
        $isQueTickets = false;
        $html = '';
        $html .= "<h3>Hello, ".$client->getFirstName().' '.$client->getLastName()."</h3>";
        $html .= "<p>Following tickets are in que for your project,</p>";
        $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;">
                         <tr>
                          <th> Ticket  </th>
                          <th> Summary </th>
                          <th> Priority</th>
                          <th> Status </th>
                        </tr>';

        $query = 'sort:priority resolution:open';
        $limit = 1000;
        for($i=1;$i<=100;$i++)
        {
            //echo 'for -'.$i.'<br/>';
            unset($tickets);
            $tickets = $this->tickets($project_permalink,$query,$i); //product_shortcode,query

            if(!is_array($tickets[0]) && count($tickets) > 0 )
            {
                $ticket = array();
                $ticket = $tickets;
                unset($tickets);
                $tickets[0] = $ticket;
            }
            if(count($tickets))
            {
                foreach ($tickets as $ticket)
                {
                    $isQueTickets = true;
                    $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$project_permalink.'/tickets/'.$ticket['ticket-id'];
                    $html .= '<tr><td><a target="_blank" href="'.$url.'">'.$project_permalink.'-'.$ticket['ticket-id'].'</a><br/>';
                    $html .= '<td align="center">'.$ticket['summary'].'</td>';
                    $html .= '<td align="center">'.$ticket['priority']['name'].'</td>';
                    $html .= '<td align="center">'.$ticket['status']['name'].'</td></tr>';
                }
            }else {
                break;
            }
            $html .= '</table>';
            if($noteTitle && $noteSubject)
            {
                $html .= '<div><h4><u>Product Owner Note</u></h4>';
                $html .= '<span><b>Title: </b>'.$noteTitle.'</span><br/>';
                $html .= '<span><b>Subject: </b>'.$noteSubject.'</span>';
                $html .= '</div><br/>';
            }
        }

        //$html .= '</table><br/>NOTE:This is autogenerated email so please do not reply to this email';
        if($isQueTickets)
        {
            $body = $html;
            $mail = Mage::getModel('core/email');
            $mail->setBody($body);
            $mail->setSubject('Tickets report by Wagento');
            $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
            $mail->setFromName("Tickets Report");
            $mail->setType('html');// You can use 'html' or 'text'

            try {
                //$mail->send();
                if (Mage::getStoreConfigFlag('system/smtp/disable')) {
                    return $this;
                }

                $zend_mail = new Zend_Mail();

                if (strtolower($mail->getType()) == 'html') {
                    $zend_mail->setBodyHtml($mail->getBody());
                }
                else {
                    $zend_mail->setBodyText($mail->getBody());
                }

                $zend_mail->setFrom($mail->getFromEmail(), $mail->getFromName())
                    ->addTo($client->getEmailAddress(), $client->getFirstName().' '.$client->getLastName())
                    ->addCc($productOwner->getEmailAddress(), $productOwner->getFirstName().' '.$productOwner->getLastName())
                    ->setSubject($mail->getSubject());
                $zend_mail->send();
            }
            catch (Exception $e) {
                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                }
            }

            $report = Mage::getModel('codebase/sentreport');
            $project = Mage::getModel('codebase/projects')->loadProjectByPermalink($project_permalink);
            $data['project_id'] = $project->getProjectId();
            $data['to_email'] = $client->getEmailAddress();
            $data['cc_email'] = $productOwner->getEmailAddress();
            $data['html_text'] = $html;
            $data['report_sent_at'] = now();
            $report->setData($data);
            try{
                $report->save();
            } catch(Exception $e) {
                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                }
            }
        }

    }

    public function sendAllProjectsReport()
    {
        $projects = $this->getProjects();
        foreach ($projects as $project)
        {
            if($project['status'] == 'active')
            {
                $project_permalink = $project['permalink'];
                $projectId = $project['project-id'];
                $loadedProject = $this->loadProjectById($projectId);
                if($loadedProject->getEntityId()) {
                    try {
                        $userId = $loadedProject->getUserId();
                        $clientId = $loadedProject->getClientId();
                        if($userId && $clientId){
                            $this->sendProjectReport($userId,$clientId,$project_permalink);
                        }
                    } catch (Exception $e) {
                        if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                            Mage::log($e->getMessage(),null,"wage_codebase.log");
                        }
                    }
                }
            }
        }
    }

    public function getTicketNotes($permalink, $ticketId) {
        $result = $this->get('/' . $permalink .'/tickets/' . $ticketId . '/notes');

        return $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
    }

    public function getFirstLastEstimates($notes) {
        $result = array('orig_estimated_time' => null, 'orig_estimator' => null, 'final_estimate' => null);

        foreach ($notes as $note) {
            if ($note['updates']
                && strstr($note['updates'], 'estimated_time_string') !== FALSE) {
                $json = json_decode($note['updates']);
                if (empty($result['orig_estimated_time'])) {
                    $user = Mage::getModel('codebase/users')->findUser($note['user-id']);
                    $result['orig_estimated_time'] = $json->estimated_time_string['1'];
                    $result['orig_estimator'] = $user->getFirstName() . " " . $user->getLastName();
                }
                $result['final_estimate'] = $json->estimated_time_string['1'];
            }
        }
        return $result;
    }

    public function updateNoteTable() {
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('total_time_spent', array('neq' => 0))
            ->addFieldToFilter('estimated_time', array('neq' => 0))
            ->setOrder('project_name','ASC');

        foreach ($collection as $ticket) {
            $result = $this->getTicketNotes($ticket->getPermalink(), $ticket->getTicketId());
            if ($notes = $result['ticket-note']) {
                $estimate = $this->getFirstLastEstimates($notes);
                try {
                    $ticket->setOrigEstimatedTime($estimate['orig_estimated_time']);
                    $ticket->setOrigEstimator($estimate['orig_estimator']);
                    $ticket->setFinalEstimate($estimate['final_estimate']);
                    $ticket->save();
                }
                catch (Exception $e) {
                    Mage::logException($e->getMessage());
                }
            }
        }
    }

    public function updateEstimatedTime()
    {
        if(Mage::getStoreConfig('codebase/tickets/update_estimate'))
        {
            $excludeProjects = explode(',',Mage::getStoreConfig('codebase/exclude/exclude_projects'));
            $this->getTickets();
            $collection = Mage::getModel('codebase/tickets')->getCollection()
                ->addFieldToFilter('resolution','open')
                ->addFieldToFilter('total_time_spent', array('neq' => 0))
                ->addFieldToFilter('estimated_time', array('neq' => 0))
                ->addFieldToFilter('total_time_spent', array ('gt' => new Zend_Db_Expr('estimated_time') ) )
                ->addFieldToFilter('project_id', array('nin' => $excludeProjects))
                ->setOrder('project_name','ASC');

            foreach($collection as $ticket){
                if($ticket->getTotalTimeSpent() > $ticket->getEstimatedTime())
                {
                        $xml = '<ticket-note>';
                        $message = "Estimated time updated through Daily cron";
                        $xml .= '<content><![CDATA['.$message.']]></content>';
                        $xml .= '<changes>';
                        $xml .= '<estimated_time_string><![CDATA['.$ticket->getTotalTimeSpent().']]></estimated_time_string>';
                        $xml .= '</changes>';
                        $xml .= '<private>1</private>';
                        $xml .= '</ticket-note>';
                    $result = $this->post('/' . $ticket->getPermalink() .'/tickets/' . $ticket->getTicketId() . '/notes', $xml);
                    $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
                    if($result['updates'])
                    {
                        $model = Mage::getModel('codebase/ticketsreport');
                        $data = array();
                        $data['ticket_id'] = $ticket->getTicketId();
                        $data['summary'] = $ticket->getSummary();
                        $data['ticket_type'] = $ticket->getTicketType();
                        $data['project_name'] = $ticket->getProjectName();
                        $data['permalink'] = $ticket->getPermalink();
                        $data['assignee'] = $ticket->getAssignee();
                        $data['reporter'] = $ticket->getReporter();
                        $data['project_id'] = $ticket->getProjectId();
                        $data['orig_estimated_time'] = $ticket->getEstimatedTime();
                        $data['updated_estimated_time'] = $ticket->getTotalTimeSpent();
                        $date = date('Y-m-d H:i:s');
                        $data['updated_at'] = $date;
                        $model->setData($data);
                        $model->save();
                        $ticket->setEstimatedTime($ticket->getTotalTimeSpent());
                        $ticket->save();
                    }
                }
            }
        }
    }

    public function updateProjectsUser()
    {
        $nowDate = Mage::getModel('core/date')->date('Y-m-d');
        $userRules = Mage::getModel('codebase/changepo')->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('effective_from', array('lteq' => $nowDate))
            ->addFieldToFilter('effective_to', array('gteq' => $nowDate))
        ;
        
        foreach ($userRules as $userRule) {
            $flag = true;
            $projects = explode(',' ,$userRule->getProjects());
            if (in_array('all', $projects)) {
                $projects = $this->getProjectsLink();
            }

            foreach ($projects as $projectLink) {
                $users = $this->projectusers($projectLink);
                //Mage::log(print_r($users, true), null, 'codebase.log');
                $userIds = array();
                foreach ($users as $user) {
                    $userIds[] = $user['id'];
                }

                $currentUser = $userRule->getCurrentUserId();
                if (in_array($currentUser, $userIds)) {
                    // change project assignee
                    /*$xml = '<users>';
                    foreach ($users as $user) {
                        if ($userRule->getCurrentUserId() != $user['id']) {
                            $xml .= '<user><id>'.$user['id'].'</id></user>';
                        }
                    }
                    $xml .= '<user><id>'.$userRule->getNewUserId().'</id></user>';
                    $xml .= '</users>';

                    //Mage::log(print_r($xml, true), null, 'codebase.log');
                    $result = $this->post('/' . $projectLink. '/assignments', $xml);
                    $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
                    */

                    //change project's tickets assignee
                    $this->changeTicketAssignee($projectLink, $userRule->getCurrentUserId(), $userRule->getNewUserId());

                    /*if (!$result) {
                        $flag = false;
                    }*/
                }
            }

            if ($flag) {
                /*$userRule->setStatus(0)
                    ->save()
                ;*/
            }
        }
    }

    public function getProjectsLink()
    {
        $projects = Mage::getModel('codebase/projects')->getCollection()
            ->addFieldToFilter('status','active')
        ;
        $projectLinks = array();
        foreach ($projects as $project) {
            $projectLinks[] = $project->getPermalink();
        }
        return $projectLinks;
    }

    public function changeTicketAssignee($projectLink, $currentUserId, $assigneeId)
    {
        $query = 'sort:priority resolution:open';
        for ($i=1;$i<=100;$i++) {
            unset($tickets);
            $tickets = $this->tickets($projectLink,$query,$i); //product_shortcode,query

            if (!is_array($tickets[0]) && count($tickets) > 0) {
                $ticket = array();
                $ticket = $tickets;
                unset($tickets);
                $tickets[0] = $ticket;
            }

            if (count($tickets)) {
                foreach ($tickets as $ticket)
                {
                    unset($data);
                    unset($avail_ticket);
                    if ($ticket['assignee-id'] == $currentUserId) {
                        Mage::log($currentUserId);
                        $xml = '<ticket-note>
                        <changes>
                            <assignee-id>'.$assigneeId.'</assignee-id>
                        </changes>
                        <private>1</private>
                    </ticket-note>'
                        ;

                        $result = $this->post('/'.$projectLink.'/tickets/'.$ticket['ticket-id'].'/notes', $xml);
                        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
                    }
                }
            } else {
                break;
            }
        }
    }

    public function sendAllDeveloperReport(){
        $query = 'sort:priority status:open';
        $overAllTime = 0;
        $developers = 0;
        $actors = array();
        $utcUsers = array();
        $utcUsers = explode(',', Mage::getStoreConfig('codebase/report/utc_users'));
        $email = explode(',',Mage::getStoreConfig('codebase/report/dailyreport_ids'));
        $limit = 10000;
        //For US and Latin America Office

        $since = date('Y-m-d 00:00:00 -0600', strtotime("-1 days") );

        $activities = $this->activity($query,$limit,$since); //product_shortcode,query
        $activities = array_reverse($activities);

        $allItems = array();
        foreach ($activities as $activity)
        {
            if($activity['type'] == 'add_time')
            {
                $changes = $activity['raw-properties']['changes'];
                $data = array();


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
                            $time             = $this->convertToHoursMins($activity['raw-properties']['minutes']);

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
            $html = '';

//            $html .= "<h3>User: ".$item[0]['actor_name']."</h3>";
//            $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;">
//                         <tr>
//                          <th> Project  </th>
//                          <th> Summary </th>
//                          <th> Time Added </th>
//                        </tr>';


            foreach($item as $ticket){
//                $html .= '<td>'.$ticket['project_name'].'</td>';
//                $html .= '<td align="center">'.$ticket['subject'].'</td>';
//                $html .= '<td align="center">'.$ticket['time_added'].'</td></tr>';
                $total_time += $ticket['time_in_minutes'];
            }
//            $html .= '<tr><td style="border-top: 2px dashed #000">&nbsp;</td>';
//            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>Total Logged Time</b></td>';
//            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>'.$this->convertToHoursMins($total_time).'</b></td></tr></table>';
            $actors[$item[0]['actor_name']] = $this->convertToHoursMins($total_time);
            $overAllTime += $total_time;
            $developers = $developers + 1;
//            $body = '<br/>'.$html;
//            $mail = Mage::getModel('core/email');
//            $mail->setToName($item[0]['actor_name']);
//            $mail->setToEmail($email);
//            $mail->setBody($body);
//            $mail->setSubject('Daily Report');
//            $mail->setFromEmail('sales@wagento.com');
//            $mail->setFromName("Daily Report");
//            $mail->setType('html');// You can use 'html' or 'text'
//
//            try {
//                $mail->send();
//            }
//            catch (Exception $e) {
//                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
//                    Mage::log($e->getMessage(),null,"wage_codebase.log");
//                }
//
//            }
        }

        // For Special users who are not in US or Latin America office
        $since = date('Y-m-d 00:00:00 -0000', strtotime("-1 days") );
        $activities = $this->activity($query,$limit,$since); //product_shortcode,query
        $activities = array_reverse($activities);

        $allItems = array();
        foreach ($activities as $activity)
        {
            //if($activity['type'] == 'ticketing_note' || $activity['type'] == 'ticketing_ticket')
            if($activity['type'] == 'add_time')
            {
                $changes = $activity['raw-properties']['changes'];
                $data = array();


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
                            $time             = $this->convertToHoursMins($activity['raw-properties']['minutes']);

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
//            $html = '';
//            $html .= "<h3>Hello, ".$item[0]['actor_name']."</h3>";
//            $html .= "<p>You have worked on following tickets yesterday,</p>";
//            $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;">
//                        <tr>
//                          <th> Project  </th>
//                          <th> Summary </th>
//                          <th> Time Added </th>
//                        </tr>';

            foreach($item as $ticket){
//                $html .= '<tr><td>'.$ticket['project_name'].'</td>';
//                $html .= '<td align="center">'.$ticket['subject'].'</td>';
//                $html .= '<td align="center">'.$ticket['time_added'].'</td></tr>';
                $total_time += $ticket['time_in_minutes'];
            }
//            $html .= '<tr><td style="border-top: 2px dashed #000">&nbsp;</td>';
//            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>Total Logged Time</b></td>';
//            $html .= '<td align="center" style="border-top: 2px dashed #000"><b>'.$this->convertToHoursMins($total_time).'</b></td></tr>';
            $actors[$item[0]['actor_name']] = $this->convertToHoursMins($total_time);
            $overAllTime += $total_time;
            $developers = $developers + 1;
//            $mail = Mage::getModel('core/email');
//            $mail->setToName($item[0]['actor_name']);
//            $mail->setToEmail($email);
//            $mail->setBody($body);
//            $mail->setSubject('Daily Report');
//            $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
//            $mail->setFromName("Daily Report");
//            $mail->setType('html');// You can use 'html' or 'text'
//
//            try {
//                $mail->send();
//            }
//            catch (Exception $e) {
//                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
//                    Mage::log($e->getMessage(),null,"wage_codebase.log");
//                }
//
//            }
        }

        $html = '';
        $html .= '<h3>Total Hours Logged previous day:- '.$this->convertToHoursMins($overAllTime).'</h3>';
        $html .= '<h3>Total number of developers who logged:- '.$developers.'</h3>';
        $html .= '<h3>Total hours per developer:- '.$this->convertToHoursMins($overAllTime/$developers).'</h3>';
        $html .= '<h3>Date:- '.date('Y-m-d', strtotime("-1 days") ).' TIME:- 12:00 AM - 11:59 PM (GMT-6:00)</h3>';

        $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;">
                        <tr>
                          <th> Developer  </th>
                          <th> Total Logged Hours </th>
                        </tr>';

        foreach($actors as $name => $time){
                $html .= '<tr><td>'.$name.'</td>';
                $html .= '<td align="center">'.$time.'</td></tr>';
        }
        $html .= '</table>';
        
            $email = explode(',',Mage::getStoreConfig('codebase/report/dailyreport_ids'));

            $body = $html;
            $mail = Mage::getModel('core/email');
            $mail->setToName('Wagento');
            $mail->setToEmail($email);
            $mail->setBody($body);
            $mail->setSubject("All Developer's Daily Report");
            $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
            $mail->setFromName("Developers Daily Report");
            $mail->setType('html');// You can use 'html' or 'text'

            try {
                $mail->send();
            }
            catch (Exception $e) {
                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                }

            }

    }

    public function sendProjectReportWithDate($userId,$clientId,$project_permalink,$noteTitle='',$noteSubject='')
    {
        $productOwner = Mage::getModel('codebase/users')->getCollection()
            ->addFieldToFilter('user_id',$userId)
            ->getFirstItem();


        $client = Mage::getModel('codebase/users')->getCollection()
            ->addFieldToFilter('user_id',$clientId)
            ->getFirstItem();
        $isQueTickets = false;
        $codebaseHelper = Mage::helper('codebase');
        $html = '';
        $html .= "<h3>Hello, ".$client->getFirstName().' '.$client->getLastName()."</h3>";
        $html .= "<p>Following tickets are in que for your project,</p>";
        $html .= '<table width="800" border="1" cellspacing="0" cellpadding="0" style="border:1px solid #ccc;margin:20px 0;">
                         <tr>
                          <th>'.$codebaseHelper->__('Ticket').'</th>
                          <th>'.$codebaseHelper->__('Summary').'</th>
                          <th>'.$codebaseHelper->__('Priority').'</th>
                          <th>'.$codebaseHelper->__('Status').'</th>
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

        if ($noteTitle && $noteSubject) {
            $html .= '<div><h4><u>Product Owner Note</u></h4>';
            $html .= '<span><b>Title: </b>'.$noteTitle.'</span><br/>';
            $html .= '<span><b>Subject: </b>'.$noteSubject.'</span>';
            $html .= '</div><br/>';
        }

        // get total hours logged
        $totalHoursLogged = $this->getLoggedTime($project_permalink);
        $html .= '<p><b>'.$codebaseHelper->__('Total Hours Logged: ').$totalHoursLogged.'</b></p>';

        // get completed tickets
        $completedTickets = $this->getCompletedTickets($project_permalink);
        $html .= '<p><b>'.$codebaseHelper->__('Tickets Completed: ').$completedTickets.'</b></p>';

        // get new tickets
        $newTickets = $this->getNewTickets($project_permalink);
        $html .= '<p><b>'.$codebaseHelper->__('New Tickets Created: ').$newTickets.'</b></p>';

        // Total Hours Left (From Estimations)
        $totalHoursLeftFromEstimation = $this->getTotalHoursLeftEstimation($project_permalink);
        $html .= '<p><b>'.$codebaseHelper->__('Total Hours Left (From Estimations): ').$totalHoursLeftFromEstimation.'</b></p>';

        // get Estimation completion date
        $estCompletionDate = $this->getEstCompletionDate($totalHoursLeftFromEstimation);
        $html .= '<p><b>'.$codebaseHelper->__('Estimation Completion Date: ').$estCompletionDate.'</b></p>';

        //$html .= '</table><br/>NOTE:This is autogenerated email so please do not reply to this email';
        if(count($tickets))
        {
            $body = $html;
            $mail = Mage::getModel('core/email');
            $mail->setBody($body);
            $mail->setSubject('Tickets report by Wagento');
            $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
            $mail->setFromName("Tickets Report");
            $mail->setType('html');// You can use 'html' or 'text'

            try {
                //$mail->send();
                if (Mage::getStoreConfigFlag('system/smtp/disable')) {
                    return $this;
                }

                $zend_mail = new Zend_Mail();

                if (strtolower($mail->getType()) == 'html') {
                    $zend_mail->setBodyHtml($mail->getBody());
                }
                else {
                    $zend_mail->setBodyText($mail->getBody());
                }

                $zend_mail->setFrom($mail->getFromEmail(), $mail->getFromName())
                    ->addTo($client->getEmailAddress(), $client->getFirstName().' '.$client->getLastName())
                    ->addCc($productOwner->getEmailAddress(), $productOwner->getFirstName().' '.$productOwner->getLastName())
                    ->setSubject($mail->getSubject());
                $zend_mail->send();
            }
            catch (Exception $e) {
                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                }
            }

            $report = Mage::getModel('codebase/sentreport');
            $project = Mage::getModel('codebase/projects')->loadProjectByPermalink($project_permalink);
            $data['project_id'] = $project->getProjectId();
            $data['to_email'] = $client->getEmailAddress();
            $data['cc_email'] = $productOwner->getEmailAddress();
            $data['html_text'] = $html;
            $data['report_sent_at'] = now();
            $report->setData($data);
            try{
                $report->save();
            } catch(Exception $e) {
                if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                    Mage::log($e->getMessage(),null,"wage_codebase.log");
                }
            }
        }

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

    /**
     * assign Ticket to $user_id and Status Change 
     */
    public function ticketAssignStatus($url, $user_id, $status_id) {
        $xml = '<ticket-note>';
        $xml .= '<changes>';
        $xml .= '<assignee-id>'.$user_id.'</assignee-id>';
        $xml .= '<status-id>'.$status_id.'</status-id>';
        $xml .= '</changes>';
        $xml .= '<private>1</private>';
        $xml .= '</ticket-note>';

        $result = $this->post($url, $xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));

        return $result;
    }

    /**
     * assign Ticket Status Change 
     */
    public function ticketChangeStatus($url, $status_id) {
        $xml = '<ticket-note>';
        $xml .= '<changes>';
        $xml .= '<status-id>'.$status_id.'</status-id>';
        $xml .= '</changes>';
        $xml .= '<private>1</private>';
        $xml .= '</ticket-note>';

        $result = $this->post($url, $xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));

        return $result;
    }

    /**
     * reassign Tickets
     */
    public function reassignticketsbacklog()
    {
        if(Mage::getStoreConfig('codebase/tickets/reassign_tickets'))
        { 
            $userCollection = Mage::getModel('codebase/users')->getCollection()
               ->addFieldToFilter('enabled', 1)
               ->addFieldToFilter('company', 'Wagento');

            foreach($userCollection as $userCollectionValues)
            {
                $userName = $userCollectionValues->getUserName();

                $ticketsCollection = Mage::getModel('codebase/tickets')->getCollection()
                    ->addFieldToFilter('resolution','open')
                    ->addFieldToFilter('assignee', $userName)
                    ->setOrder('priority_name','ASC');

                $ticketsCollection->getSelect()->joinLEFT(Mage::getConfig()->getTablePrefix().'codebase_projects', 'main_table.project_id ='.Mage::getConfig()->getTablePrefix().'codebase_projects.project_id',array('codebase_projects.backlog_id'));

                $ticketAssignId = array();
                $assigneId = '';
                $remainingTime = 0; 
                foreach($ticketsCollection as $ticketCollectionValue)
                {
                    
                    $flag = 0;    
                    $estimatedTime = (int)$ticketCollectionValue->getEstimatedTime();
                    $totalTimeSpent = (int)$ticketCollectionValue->getTotalTimeSpent();
                        
                    if($ticketCollectionValue->getStatusName() == 'In Progress' && ($estimatedTime > 0))
                    {
                       
                        $remainingTime = $remainingTime + ($estimatedTime - $totalTimeSpent); 

                         if( ($ticketCollectionValue->getPriorityName() == 'Critical' && ($remainingTime <= 420))
                            || ($ticketCollectionValue->getPriorityName() == 'High' && ($remainingTime <= 420))                  
                            || ($ticketCollectionValue->getPriorityName() == 'Normal' && ($remainingTime <= 420))                   
                            || ($ticketCollectionValue->getPriorityName() == 'Low' && ($remainingTime <= 420))                   
                            || ($ticketCollectionValue->getPriorityName() == 'Phase 2' && ($remainingTime <= 420)) ){                
                           
                        }else{
                            $flag = 1;    
                        }

                 
                    }else{
                        $flag = 1;           
                    }

                    if($flag == 1)
                    {
                        $assigneId = $ticketCollectionValue->getBacklogId();  
                        if($assigneId){
                            $status_id = '4385873';
                            $url = '/'.$ticketCollectionValue->getPermalink().'/tickets/'.$ticketCollectionValue->getTicketId().'/notes';
                            $ticketAssigned = Mage::getModel('codebase/codebase')->ticketAssignStatus($url, $assigneId, $status_id);
                        }
                        //$user[$userName][] = $ticketCollectionValue->getTicketId(); 
                    }
                    
                } 

            }
            //print_r($user);
           
        }

    }

     /**
     * Export codebase database
     */
    public function backupcodebase()
    { 
        $config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");
        $dbinfo = array(
            "host" => $config->host,
            "user" => $config->username,
            "pass" => $config->password,
            "dbname" => $config->dbname
        );

        // Database Config
        $db_host = $dbinfo["host"];
        $db_user = $dbinfo["user"];
        $db_pass = $dbinfo["pass"];
        $db_name = $dbinfo["dbname"];

        $exportDir = Mage::getBaseDir('var') . DS . "backups" . DS;
        $backup_file = $exportDir . "codebase_". date("Y-m-d-H-i-s") . ".sql.gz";

        $file = new Varien_Io_File();
        $importReadyDirResult = $file->mkdir($exportDir);

        //$command = "mysqldump --database " . $db_name  . " -u ". $db_user  . " -p'". $db_pass . "' | gzip > " . $backup_file;
        $command = "mysql ".$db_name." -u ".$db_user." -p'".$db_pass."' -e 'show tables like \"codebase_%\"' | grep -v Tables_in | xargs mysqldump ".$db_name." -u ".$db_user." -p'".$db_pass."' | gzip > $backup_file";
        $output = shell_exec($command);
    }

    public function sendCriticalHighTicketsReports()
    {
        $html = "";
        $projectName = '';
        $priority = array("Critical","High");
        $collection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('priority_name',array('in' => $priority));

        /*prepare list of exclude statuses - apply status filter*/
        $exclude = explode(';', Mage::getStoreConfig('codebase/tickets/billable_statuses'));
        foreach ($exclude as $status) {
            if (!empty($status)) $collection->addFieldToFilter('status_name', array('nlike' => "%{$status}%"));
        }

        /*get apply active projects filter if there is data*/
        $active = Mage::getModel('codebase/projects')->getActiveIds();
        if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));


        foreach($collection as $item){
            if(($projectName == '')|| ($item['project_name'] != $projectName)){
                $projectName = $item['project_name'];
                $html .= '<h3>'.$projectName.'</h3>';
            }
            $project_permalink = $item['permalink'];
            $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$project_permalink.'/tickets/'.$item['ticket_id'];
            $html .= '<b>'.$item['priority_name'].' : </b><a target="_blank" href="'.$url.'">'.$project_permalink.'-'.$item['ticket_id'].'</a><br/>';
        }
        $email = explode(',',Mage::getStoreConfig('codebase/report/criticalhigh_recipient'));
        $body = $html;
        $mail = Mage::getModel('core/email');
        $mail->setToName('Tickets');
        $mail->setToEmail($email);
        $mail->setBody($body);
        $mail->setSubject('Critical and High Priority Tickets');
        $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
        $mail->setFromName("Critical and High Priority Tickets");
        $mail->setType('html');// You can use 'html' or 'text'

        try {
            $mail->send();
        }
        catch (Exception $e) {
            if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                Mage::log($e->getMessage(),null,"wage_codebase.log");
            }

        }
    }

    public function sendTicketUpdateToProductOwnerReport()
    {
        $productOwner = explode(',',Mage::getStoreConfig('codebase/backlog/project_owner'));
        if(count($productOwner) >= 1){
                   
            foreach($productOwner as $productOwnerUser)
            {
                $html = "";
                $projectName = '';
                $dated = date('Y-m-d H:i:s', strtotime('-1 days', time())); 

                $collection = Mage::getModel('codebase/tickets')->getCollection()
                ->addFieldToFilter('resolution','open')
                ->addFieldToFilter('updated_at', array('lteq' => $dated))
                ->setOrder('project_name','ASC');

                $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'codebase_users', 'main_table.assignee ='.Mage::getConfig()->getTablePrefix().'codebase_users.user_name',array('company'=>'company','email_address'=>'email_address','first_name'=>'first_name','last_name'=>'last_name'))
                ->where('company = "Wagento" ');

                /*get apply active projects filter if there is data*/
                $active = Mage::getModel('codebase/projects')->getActiveIds();
                if (!empty($active)) $collection->addFieldToFilter('project_id' , array('in' => $active));

                $collection->addFieldToFilter('codebase_users.user_id' , $productOwnerUser);

                foreach($collection as $item)
                {
                    if(($projectName == '')|| ($item['project_name'] != $projectName)){
                        $projectName = $item['project_name'];
                        $html .= '<h3>'.$projectName.'</h3>';
                    }
                    $project_permalink = $item['permalink'];
                    $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$project_permalink.'/tickets/'.$item['ticket_id'];
                    $html .= '<b>'.$item['priority_name'].' : </b><a target="_blank" href="'.$url.'">'.$project_permalink.'-'.$item['ticket_id'].'</a><br/>';

                    $username = $item['first_name'].' '.$item['last_name'];
                }
                if($html)
                {
                    $email = trim($item['email_address']); 
                    $body = $html;
                    $mail = Mage::getModel('core/email');
                    $mail->setToName($username);
                    $mail->setToEmail($email);
                    $mail->setBody($body);
                    $mail->setSubject('Tickets Needs updates from Product Owner');
                    $mail->setFromEmail(Mage::getStoreConfig('codebase/general/fromemail'));
                    $mail->setFromName("Codebase");
                    $mail->setType('html'); // You can use 'html' or 'text'

                    try {
                        $mail->send();
                    }
                    catch (Exception $e) {
                        if(Mage::getStoreConfigFlag("codebase/general/codebaselog")) {
                            Mage::log($e->getMessage(),null,"wage_codebase.log");
                        }

                    }
                }
                
            }
        }
    }

    /**
     * reassign to backlog if tickets available with developer for more than 1 day 
     */
    public function extraTicketsReassignBacklog()
    {
        if(Mage::getStoreConfig('codebase/tickets/extratickets_reassign'))
        {
            $dated = date('Y-m-d H:i:s', strtotime('-1 days', time())); 

            $userCollection = Mage::getModel('codebase/users')->getCollection()
               ->addFieldToFilter('enabled', 1)
               ->addFieldToFilter('user_name', array('nlike' => '%backlog%'))
               ->addFieldToFilter('company', 'Wagento');

            foreach($userCollection as $userCollectionValues)
            {
                $userName = $userCollectionValues->getUserName();

                $ticketsCollection = Mage::getModel('codebase/tickets')->getCollection()
                    ->addFieldToFilter('resolution','open')
                    ->addFieldToFilter('assignee', $userName)
                    ->addfieldtofilter('updated_at', array('lteq' => $dated))
                    ->setOrder('priority_name','ASC');

                $active = Mage::getModel('codebase/projects')->getActiveIds();
                if (!empty($active)) $ticketsCollection->addFieldToFilter('main_table.project_id' , array('in' => $active));    

                $ticketsCollection->addFieldToFilter('status_name', array(
                                                                        array('like'=>'%Developer : Needs more work%'),
                                                                        array('like'=>'%New%')
                                                                    ));
                

                $ticketsCollection->getSelect()->joinLEFT(Mage::getConfig()->getTablePrefix().'codebase_projects', 'main_table.project_id ='.Mage::getConfig()->getTablePrefix().'codebase_projects.project_id',array('codebase_projects.backlog_id'));


                foreach($ticketsCollection as $ticketCollectionValue)
                {
                    $assigneId = $ticketCollectionValue->getBacklogId();  
                    if($assigneId){
                        $status_id = '4121618';
                        $url = '/'.$ticketCollectionValue->getPermalink().'/tickets/'.$ticketCollectionValue->getTicketId().'/notes';
                        $ticketAssigned = Mage::getModel('codebase/codebase')->ticketAssignStatus($url, $assigneId, $status_id);
                    }
                }    

            }
        }

    }
}
