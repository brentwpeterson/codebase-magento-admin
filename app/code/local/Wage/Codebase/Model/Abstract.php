<?php

class Wage_Codebase_Model_Abstract {

	public function __construct($username,$password,$hostname,$secure=null,$mode=null){

        $this->username = $username;
        $this->password = $password;
        $this->hostname = Mage::getStoreConfig('codebase/general/host');
        $this->secure = 's';
        $user = Mage::getSingleton('admin/session');

        if($user->getUser())
        {
            $this->api_user = $user->getUser()->getApiUser();
            $this->api_key = $user->getUser()->getApiKey();
            if(!$this->api_user || !$this->api_key) {
                $this->api_user = Mage::getStoreConfig('codebase/general/apiuser');
                $this->api_key = Mage::getStoreConfig('codebase/general/apikey');
            }
        } else {
            $this->api_user = Mage::getStoreConfig('codebase/general/apiuser');
            $this->api_key = Mage::getStoreConfig('codebase/general/apikey');
        }

        $this->debug = Mage::getStoreConfigFlag("codebase/general/codebaselog");;
        if($mode==null) {
            $this->mode = 'apikey';
        } elseif($mode=='userpass') {
            $this->mode = 'userpass';
        }
        $this->url = 'http'.$this->secure.'://api3.codebasehq.com';

	}
	
	public function debugLog($log){
		if($this->debug){
			Mage::log($log,null,"wage_codebase.log");
		}
	}

    public function projects() {
        $projects = $this->get('/projects');
        if($projects===false) return false;
        $xml = $this->object2array(simplexml_load_string($projects,'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['project'];
    }

    public function users() {
        $users = $this->get('/users');
        if($users === false) return false;
        $xml = $this->object2array(simplexml_load_string($users,'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['user'];
    }

    public function projectusers($permalink) {
        $projects = $this->get('/'.$permalink.'/assignments');
        if($projects===false) return false;
        $xml = $this->object2array(simplexml_load_string($projects,'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['user'];
    }

    private function request($url=null,$xml=null,$post) {
        $this->debugLog("url: ".$this->url.$url);
        $ch = curl_init($this->url.$url);

        $cert =  Mage::getBaseDir().'/js/cert/COMODORSACertificationAuthority';
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, $cert);

        if($post) {
            curl_setopt($ch, CURLOPT_POST, $post);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        }
        $headers = array(
            'Content-Type: application/xml',
            'Accept: application/xml'
        );
        try {
        if($this->mode=='apikey') {
            $headers[] = 'Authorization: Basic ' . base64_encode($this->api_user . ':'. $this->api_key);
        } else {
            curl_setopt($ch, CURLOPT_USERPWD, $this->hostname . '/'.$this->username . ':' . $this->password);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        } catch (Exception $e){
            $this->debugLog("error: ".$e->getMessage());
        }
        $this->debugLog("response: ".$output);
        if(!$output || strlen($output)==1) {
//echo "Error. ".curl_error($ch);
            return false;
        } else {
            return $output;
        }
        curl_close($ch);
    }

    private function putrequest($url=null,$xml=null) {
        $this->debugLog("url: ".$this->url.$url);
        $ch = curl_init($this->url.$url);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        $headers = array(
            'Content-Type: application/xml',
            'Accept: application/xml'
        );
        try {
            if($this->mode=='apikey') {
                $headers[] = 'Authorization: Basic ' . base64_encode($this->api_user . ':'. $this->api_key);
            } else {
                curl_setopt($ch, CURLOPT_USERPWD, $this->hostname . '/'.$this->username . ':' . $this->password);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
        } catch (Exception $e){
            $this->debugLog("error: ".$e->getMessage());
        }
        $this->debugLog("response: ".$output);
        if(!$output || strlen($output)==1) {
//echo "Error. ".curl_error($ch);
            return false;
        } else {
            return $output;
        }
        curl_close($ch);
    }

    public function tickets($permalink,$find,$page) {
        $params = array(
            'query' => $find,
            'page' => $page,
        );
        //$url = '/'.$permalink.'/tickets?query=sort:priority status:open';
        $url = '/'.$permalink.'/tickets?'.http_build_query($params);
        $xml = $this->object2array(simplexml_load_string($this->get($url),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['ticket'];
    }

    public function activity($find,$limit, $since,$permalink=null) {
        $params = array(
            'raw' => 'true',
            'query' => $find,
            'limit' => $limit,
            'since' => $since
        );
        if($permalink){
            $url = '/'.$permalink.'/activity?'.http_build_query($params);
        } else {
            $url = '/activity?'.http_build_query($params);
        }
        $xml = $this->object2array(simplexml_load_string($this->get($url),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['event'];
    }

    /**
     * Query time tracking from codebase
     * @author atheotsky
     */
    public function timetracking($project, $type = null) {
        switch ($type) {
        case 'day':
            $period = '/day';
            break;
        case 'week':
            $period = '/week';
            break;
        case 'month':
            $period = '/month';
            break;
        default:
            $period = '';
            break;
        }

        $xml = $this->object2array(
            simplexml_load_string(
                $this->get('/'.$project.'/time_sessions'.$period),
                'SimpleXMLElement',
                LIBXML_NOCDATA)
        );

        return $xml['time-session'];
    }

    public function project($permalink) {
        $xml = $this->object2array(simplexml_load_string($this->get('/'.$permalink),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml;
    }
    public function notes($ticketId,$project) {
        $xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/'.$ticketId.'/notes'),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['ticket-note'];
    }
    public function statuses($project) {
        $xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/statuses'),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['ticketing-status'];
    }
    public function categories($project) {
        $xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/categories'),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['ticketing-category'];
    }
    public function priorities($project) {
        $xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/priorities'),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['ticketing-priority'];
    }
    public function milestones($project) {
        $xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/milestones'),'SimpleXMLElement',LIBXML_NOCDATA));
        return $xml['ticketing-milestone'];
    }
    public function addTimeEntry($project,$params) {
        $xml = '<time-session>';
        foreach($params as $key=>$value) {
            if($key=='minutes') {
                $attributes = ' type=\'integer\'';
            } elseif($key=='session-date') {
                $attributes = ' type=\'date\'';
            } else {
                $attributes = null;
            }
            $xml .= '<'.$key.$attributes.'>'.$value.'</'.$key.'>';
        }
        $xml .= '</time-session>';
        $result = $this->post('/'.$project.'/time_sessions',$xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
        return $result;
    }
    public function addTicket($project,$params,$files) {
        $xml = '<ticket>';
        foreach($params as $key=>$value) {
            $xml .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
        }
        $xml .= '</ticket>';
        $result = $this->post('/'.$project.'/tickets',$xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
        return $result;
    }
    public function addAttachments($project,$files,$ticketId) {
        $result = null;
        foreach($files as $file) {
            $post_array['ticket_attachment[attachment]'] = '@'.$file['tmp_name'].';type='.$file['type'];
            $post_array['ticket_attachment[description]'] = $file['name'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url.'/'.$project.'/tickets/'.$ticketId.'/attachments.xml');
            curl_setopt($ch, CURLOPT_USERPWD, $this->hostname . '/'.$this->username . ':' . $this->password);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result .= curl_exec($ch);
        }
        return $result;
    }
    public function note($project,$note,$ticketId,$changes=array(),$minutes=null) {
        $xml = '<ticket-note>';
        $xml .= '<content><![CDATA['.$note.']]></content>';
        if($minutes!=null) {
            $xml .= '<time-added><![CDATA['.$minutes.']]></time-added>';
        }
        if(!empty($changes)) {
            $xml .= '<changes>';
            foreach($changes as $key=>$value) {
                $xml .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
            }
            $xml .= '</changes>';
        }
        $xml .= '<private>1</private>';
        $xml .= '</ticket-note>';
        $result = $this->post('/'.$project.'/tickets/'.$ticketId.'/notes',$xml);
        $result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
        return $result;
    }


    protected function post($url=null,$xml=null) {
        return $this->request($url,$xml,1);
    }
    protected function put($url=null,$xml=null) {
        return $this->putrequest($url,$xml);
    }
    protected function get($url=null) {
        return $this->request($url,null,0);
    }

    protected function object2array($object) { return @json_decode(@json_encode($object),1); }
}

