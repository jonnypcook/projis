<?php

namespace Application\Service;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class GoogleService 
{
    protected $config;
    
    /**
     * project
     * @var \Project\Entity\Project
     */
    protected $project;
    
    /**
     * client
     * @var \Client\Entity\Client 
     */
    protected $client;


    /**
     * user
     * @var \Application\Entity\User 
     */
    protected $user;
    

    /**
     * google api object
     * @var \Google_Client 
     */
    private $google;

    
    public function __construct($config, \Application\Entity\User $user, \Doctrine\ORM\EntityManager $em) {
        $this->setConfig($config);
        $this->setUser($user);
        $this->setEntityManager($em);
    }
    
    
    /**
     * get the google oauth client
     * @param type $autoRefresh
     * @return \Google_Client
     */
    public function getGoogleClient($autoRefresh=true) {
        if (!($this->google instanceof \Google_Client)) {
            // grab local config
            $config = $this->getConfig();
            $this->google = new \Google_Client();
            $this->google->setAccessToken($this->getUser()->getToken_access());
            $this->google->setClientId($config['clientId']);
            $this->google->setClientSecret($config['clientSecret']);
            $this->google->setAccessType($config['accessType']);
            $this->google->setRedirectUri($config['redirectUri']);
            $this->google->setScopes($config['scope']);
            
        }
        
        if ($autoRefresh) {
            if ($this->google->isAccessTokenExpired()) {
                try {
                    $this->google->refreshToken($this->getUser()->getToken_refresh());
                    $this->getUser()->setToken_access($this->google->getAccessToken());
                    $this->getEntityManager()->persist($this->getUser());
                    $this->getEntityManager()->flush();
                } catch (\Exception $e) {
                    // do nothing
                }
            }
        }
        
        return $this->google;
    }
    
    /**
     * check for google oauth2 (stored) status
     * @return boolean
     */
    public function hasGoogle() {
        if (!$this->getUser()->getGoogleEnabled()) {
            return false;
        }
        
        if (empty($this->getUser()->getToken_refresh())) {
            return false;
        }
        
        if (empty($this->getUser()->getToken_access())) {
            return false;
        }
        
        return true;
    }
    
    /**
     * revoke google oauth2 token
     */
    public function revokeGoogle() {
        try {
            if (!empty($this->getUser()->getToken_access())) {
                $config = $this->getConfig();

                $this->google = new \Google_Client();
                $this->google->setAccessToken($this->getUser()->getToken_access());
                $this->google->setClientId($config['clientId']);
                $this->google->setClientSecret($config['clientSecret']);
                $this->google->setAccessType($config['accessType']);
                $this->google->setRedirectUri($config['redirectUri']);
                $this->google->setScopes($config['scope']);

                $this->google->revokeToken();
            }
        } catch (\Exception $e) {
            
        }
        $this->getUser()->setToken_access(null);
        $this->getUser()->setToken_refresh(null);
        $this->getEntityManager()->persist($this->getUser());
        $this->getEntityManager()->flush();
     }
    
    
    
    /*
     * CalendarAPI Access Methods
     */
    
    /**
     * list calendar events between 2 dates
     * @param array $config
     * @return array
     * @throws \Exception
     */ 
    public function findCalendarEvents(array $config=array()) {
        if (!$this->hasGoogle()) {
            throw new \Exception('Google client unavailable');
        }
        
        $params = array();
        
        if (!empty($config['start'])) {
            $params['timeMin'] = date('c', $config['start']);
        }
        
        if (!empty($config['end'])) {
            $params['timeMax'] = date('c', $config['end']);
        }
        
        if (!empty($config['start']) && !empty($config['end'])) {
            if ($config['start'] > $config['end']) {
                throw new \Exception('invalid parameters');
            }
        }
        
        $client = $this->getGoogleClient();
            
        // calendar
        $owner = !empty($config['owner'])?$config['owner']:$this->getUser()->getEmail();
        $calendar = new \Google_Service_Calendar($client);
        $evts = $calendar->events->listEvents($owner, $params);

        if (!($evts instanceof \Google_Service_Calendar_Events)) {
            throw new \Exception('no results');
        }

        $data = array();
        $colours = array(
            'yellow'=>array (
                'textColor'=>'#c09853',
                'backgroundColor'=>'#fcf8e3',
                'borderColor'=>'#fbeed5',
            ),
            'green'=>array (
                'textColor'=>'#468847',
                'backgroundColor'=>'#dff0d8',
                'borderColor'=>'#d6e9c6',
            ),
            'red'=>array (
                'textColor'=>'#b94a48',
                'backgroundColor'=>'#f2dede',
                'borderColor'=>'#eed3d7',
            ),
            'blue'=>array (
                'textColor'=>'#3a87ad',
                'backgroundColor'=>'#d9edf7',
                'borderColor'=>'#bce8f1',
            ),
        );
        if ($evts->count()) {
            foreach ($evts as $event) {
                if (!empty($event->getstart())) {
                    if (!empty($event->getstart()->getdate())) {
                        $start = $event->getstart()->getdate();
                        $end = $event->getend()->getdate();
                    } else {
                        $start = $event->getstart()->getdatetime();
                        $end = $event->getend()->getdatetime();
                    }
                } else {
                    continue;
                }

                $owner = ($event->getCreator()->getEmail()==$owner);
                
                $item = array (
                    'title'=>$event->getSummary(),
                    'start'=>$start,
                    'end'=>$end,
                    'id'=>$event->getId(),
                    'description'=>$event->getDescription(),
                )+$colours[$owner?'green':'blue'];/**/
                
                if (!empty($config['linkable'])) {
                    $item['url'] = '/calendar/advancededit/?eid='.$event->getId();
                }
                
                $data[] = $item;
            }
        }
        
        return $data;
        
    }
    
    /**
     * list calendar events between 2 dates
     * @param array $config
     * @return \Google_Service_Calendar_Event
     * @throws \Exception
     */ 
    public function findCalendarEvent($eventId, array $config=array()) {
        if (!$this->hasGoogle()) {
            throw new \Exception('Google client unavailable');
        }
        
        $params = array();
        
        $client = $this->getGoogleClient();
            
        // calendar
        $calendar = new \Google_Service_Calendar($client);
        
        $event = $calendar->events->get($this->getUser()->getEmail(), $eventId, $params);
        
        
        if (!($event instanceof \Google_Service_Calendar_Event)) {
            throw new \Exception('no results');
        }

        return $event;
    }
    
    
    /**
     * list calendar events between 2 dates
     * @param array $config
     * @return \Google_Service_Calendar_Event
     * @throws \Exception
     */ 
    public function deleteCalendarEvent($eventId, array $config=array()) {
        if (!$this->hasGoogle()) {
            throw new \Exception('Google client unavailable');
        }
        
        $params = array();
        
        $client = $this->getGoogleClient();
            
        // calendar
        $calendar = new \Google_Service_Calendar($client);
        
        $params['sendNotifications']=true;
        $response = $calendar->events->delete($this->getUser()->getEmail(), $eventId, $params);
        

        return $response;
    }
    
    /**
     * Add a calendar event
     * @param string $title
     * @param int $tmStart
     * @param int $tmEnd
     * @param array $config
     * @return array
     * @throws \Exception
     */
    public function addCalendarEvent($title, $tmStart, $tmEnd, array $config=array()) {
        if (!$this->hasGoogle()) {
            throw new \Exception('Google client unavailable');
        }
        
        $updateMode = (!empty($config['event']) && ($config['event'] instanceof \Google_Service_Calendar_Event));
        
        $client = $this->getGoogleClient();
        $calendar = new \Google_Service_Calendar($client);

        $event = $updateMode?$config['event']:/**/new \Google_Service_Calendar_Event();
        $event->setSummary($title);
        
        if (!empty($config['location'])) {
            $event->setLocation($config['location']);
        }
         
        // set description
        if ($this->hasProject()) {
            $event->setDescription((empty($config['description'])?'':$config['description'].' | ').'Arranged for project: "'.$this->getProject()->getName().'" of client: "'.$this->getProject()->getClient()->getName().'" '
                    . ' | http://projis.8p3.co.uk/client-'.$this->getProject()->getClient()->getClientId().'/project-'.$this->getProject()->getProjectId().'/');
        } elseif ($this->hasClient()) {
            $event->setDescription((empty($config['description'])?'':$config['description'].' | ').'Arranged for client: "'.$this->getClient()->getName().'"'
                    . ' | http://projis.8p3.co.uk/client-'.$this->getClient()->getClientId().'/');
        }elseif (!empty($config['description'])) {
            $event->setDescription($config['description']);
        }
        
        //echo $event->getDescription();
        
        //die('STOP HERE!!');
        
        $attendees = array();
        $existingEmails = array();
        if ($updateMode) {
            if (!empty($config['attendees'])) {
                foreach ($config['attendees'] as $idx=>$val) {
                    $config['attendees'][$idx] = strtolower($val);   
                }
                foreach ($event->getAttendees() as $attendee) {
                    if (in_array($attendee->getEmail(), $config['attendees'])) {
                        $attendees[] = $attendee;
                        $existingEmails[$attendee->getEmail()] = true;
                    }
                }
            }
        }
        $event->attendees = array();
        
        if (!empty($config['attendees'])) {
            foreach ($config['attendees'] as $email) {
                if (!empty($existingEmails[$email])) {
                    continue;
                }
                $attendee = new \Google_Service_Calendar_EventAttendee();
                $attendee->setEmail($email);
                $attendees[] = $attendee;
            }/**/
            
            $event->setAttendees($attendees);
        }

        if (!empty($config['allday'])) {
            $start = new \Google_Service_Calendar_EventDateTime();
            $start->setDate(date('Y-m-d', $tmStart));
            $event->setStart($start);
            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDate(date('Y-m-d', $tmEnd));
            $event->setEnd($end);    
        } else {
            $start = new \Google_Service_Calendar_EventDateTime();
            $start->setDateTime(date('c', $tmStart));
            $event->setStart($start);
            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDateTime(date('c', $tmEnd));
            $event->setEnd($end);                    
        }
        
        $params = array();
        if (!empty($config['notifications'])) {
            $params['sendNotifications']=true;
        }
        
        if ($updateMode) {
            $createdEvent = $calendar->events->update($this->getUser()->getEmail(), $event->getId(), $event, $params); //Returns array not an object
        } else {
            $createdEvent = $calendar->events->insert($this->getUser()->getEmail(), $event, $params); //Returns array not an object
        }
        
        $event = array('info'=>array(
            'id'=>$createdEvent->id,
            'title'=>$title,
            'start'=>date('Y-m-d'.(!empty($config['allday'])?'':' H:i'),$tmStart),
            'end'=>date('Y-m-d'.(!empty($config['allday'])?'':' H:i'),$tmEnd),
        ));
        
        return $event;
    }
    
   
    
    /*
     * GMailAPI Access Methods
     */
    /**
     * list gmail threads
     * @param array $config
     * @param boolean $saveThreadCount
     * @param boolean $countOnly
     * @return string|array
     * @throws \Exception
     */
    public function findGmailThreads(array $config=array(), $saveThreadCount=false, $countOnly=false) {
        if (!$this->hasGoogle()) {
            throw new \Exception('Google client unavailable');
        }
        
        if ($countOnly && $saveThreadCount && empty($config['refresh'])) {
            $usrConfig = $this->getUser()->getConfig();
            if (!empty($usrConfig)) {
                $usrConfig = json_decode($usrConfig);
                if (isset($usrConfig->gmailCount) && isset($usrConfig->gmailExpire)) {
                    if (time() < $usrConfig->gmailExpire) {
                        $data['count'] = $usrConfig->gmailCount;
                        $data['msg'] = array();
                        return ($data);/**/
                    }
                }
            }
        }
        
        $data = array();
            
        $client = $this->getGoogleClient();
        $mail = new \Google_Service_Gmail($client);

        
        // gmail search param api = https://support.google.com/mail/answer/7190?hl=en
        $searchQuery = array();
        if (!empty($config['in'])) {
            $searchQuery[] = 'in:'.$config['in'];
        }
        
        if (!empty($config['unread'])) {
            $searchQuery[] = 'is:unread';
        }
        
        if ($this->hasProject()) {
            $searchQuery[] = 'subject:'.str_pad($this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT).'-'.str_pad($this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT).'';
        }
        
        if (!empty($config['subject'])) {
            $searchQuery[] = 'subject:'.$config['subject'];
        }
        
        if (!empty($config['from'])) {
            $searchQuery[] = 'from:'.$config['from'];
        }
        
        if (!empty($config['to'])) {
            $searchQuery[] = 'to:'.$config['to'];
        }
        
        if (!empty($config['label'])) {
            $searchQuery[] = 'label:'.$config['label'];
        }
        
        $openThreads = $mail->users_threads->listUsersThreads($this->getUser()->getEmail(), array (
            'q'=>implode(' ', $searchQuery),
            //'includeSpamTrash'=>'false', // we are never interested in spam or trash
            'maxResults'=>empty($config['maxResults'])?10:$config['maxResults'], 
        ));
        
        //echo '<pre>', print_r($openThreads, true),'</pre>';die(); 

        $data['count'] = $openThreads->resultSizeEstimate;

        // TODO: possibly need to rethink this one!
        if ($saveThreadCount) {
            $this->getUser()->addConfigProperty('gmailCount', $openThreads->resultSizeEstimate);
            $this->getUser()->addConfigProperty('gmailExpire', time()+(60*60*30));

            $this->getEntityManager()->persist($this->getUser());
            $this->getEntityManager()->flush();
        }

        $data['msg'] = array();
        
        if ($countOnly) {
            return $data;
        }
        
        foreach ($openThreads as $thread) {
            $threadId = $thread->id;
            $messages = $mail->users_threads->get($this->getUser()->getEmail(), $thread->id, array('fields'=>'messages'));
            $thread = array();
            foreach ($messages as $message) {
                $msg = array();
                foreach ($message->payload->headers as $header) {
                    switch (strtolower($header->name)) {
                        case 'from':
                            $msg[strtolower($header->name)] = preg_replace('/[ ]*[<][^>]+[>]$/', '', $header->value);
                            break;
                        case 'subject':
                            $msg[strtolower($header->name)] = $header->value;
                            break;
                        case 'date':
                            $tm = strtotime($header->value);

                            $hrs = floor((time()-$tm)/(60*60));
                            if ($hrs==0) {
                                $tmMsg = 'Just Now';
                            } elseif ($hrs<24) {
                                $tmMsg = $hrs.' hours ago';
                            } else {
                                $tmMsg = floor($hrs/24).' days ago';
                            }

                            $msg[strtolower($header->name)] = $tmMsg;
                            break;
                    }
                }
                
                if (!empty($msg)) {
                    $thread[$message->id] = $msg;
                }
                break;
            }
            $data['msg'][$threadId] = $thread;
            //echo '<pre>', print_r($data, true),'</pre>';; 
           // echo '<pre>', print_r($messages, true),'</pre>';
        }    
        return $data;
    }
    
    
    /**
     * list gmail threads
     * @param array $config
     * @param boolean $saveThreadCount
     * @param boolean $countOnly
     * @return string|array
     * @throws \Exception
     */
    public function findGmailThread($threadId, array $config=array()) {
        if (!$this->hasGoogle()) {
            throw new \Exception('Google client unavailable');
        }
        
        $data = array();
            
        $client = $this->getGoogleClient();
        $mail = new \Google_Service_Gmail($client);

        // gmail search param api = https://support.google.com/mail/answer/7190?hl=en
        
        
        
        $thread = $mail->users_threads->get($this->getUser()->getEmail(), $threadId);
        //\Google_Service_Gmail_MessagePartBody::
        $messages = array();
        if (!empty($thread->messages)) {
            $idx = 0;
            foreach ($thread->messages as $message) {
                $messages[$idx] = array('id'=>$message->id);
                if (!empty($message->payload->body->size)) {
                    $messages[$idx]['body'] = base64_decode(strtr($message->payload->body->getData(), '-_', '+/'));
                    $messages[$idx]['bodylen'] = strlen($message->payload->body->getData());
                } elseif (!empty($message->payload->parts)) {
                    foreach ($message->payload->parts as $part) {
                        if ($part->mimeType == 'text/plain') {
                            $messages[$idx]['body'] = base64_decode(strtr($part->body->getData(), '-_', '+/'));
                        } elseif ($part->mimeType == 'text/html') {
                            $messages[$idx]['body'] = base64_decode(strtr($part->body->getData(), '-_', '+/'));
                            break; // we would rather have html version
                        }
                    }
                }

                foreach ($message->payload->headers as $header) {
                    switch (strtolower($header->name)) {
                        case 'to':
                        case 'cc':
                        case 'from':
                            $messages[$idx][strtolower($header->name)] = preg_replace('/[ ]*[<][^>]+[>]$/', '', $header->value);
                            break;
                        case 'subject':
                            $messages[$idx][strtolower($header->name)] = $header->value;
                            break;
                        case 'date':
                            $tm = strtotime($header->value);

                            $hrs = floor((time()-$tm)/(60*60));
                            if ($hrs==0) {
                                $tmMsg = 'Just Now';
                            } elseif ($hrs<24) {
                                $tmMsg = $hrs.' hour'.(($hrs==1)?'':'s').' ago';
                            } else {
                                $tmp = floor($hrs/24);
                                $hrs = $hrs-($tmp*24);
                                $tmMsg = $tmp.' day'.(($tmp==1)?'':'s').' '.(($hrs>0)?$hrs.' hour'.(($hrs==1)?'':'s').' ':'').'ago';
                            }

                            $messages[$idx][strtolower($header->name)] = $tmMsg;
                            $messages[$idx]['datesent'] = date('l jS F, Y \a\t H:i', $tm);
                            break;
                    }
                }
                $idx++;
            }
        }
        
        return ($messages);
        //return array_reverse($messages);

    }
    
    public function sendGmail ($subject, $body, array $to, array $params=array()) {
        if (empty($to)) {
            throw new \Exception ('to address not found');
        }
        
        // we use PHPMailer to compose the emails before sending through gmail API
        $mail = new \PHPMailer();
        $mail->CharSet = "UTF-8";
        
        $mail->From = $this->getUser()->getEmail();
        $mail->FromName = $this->getUser()->getName();
        
        $config = $this->getConfig();
        $testMode = !empty($config['test']);
        if (!$testMode) {
            foreach ($to as $email) {
                $mail->AddAddress($email);
            }
        } else {
            $mail->AddAddress($config['testEmail']);
        }
        
        if (!empty($params['cc']) && !$testMode) {
            foreach ($params['cc'] as $email) {
                $mail->addCC($email);
            }
        }
        
        if (!empty($params['bcc']) && !$testMode) {
            foreach ($params['bcc'] as $email) {
                $mail->addBCC($email);
            }
        }
        
        $mail->AddReplyTo($this->getUser()->getEmail(), $this->getUser()->getName());
        
        $mail->Subject = ($testMode?'TEST MODE - ':'').$subject.($this->hasProject()?' ['.str_pad($this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT).'-'.str_pad($this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT).']':'');
        $mail->Body    = $body;
        $mail->isHTML(true);

        if (!empty($params['attachment'])) {
            if (!is_array($params['attachment'])) {
                $params['attachment'] = array($params['attachment']);
            }
            
            foreach ($params['attachment'] as $attachment) {
                $mail->addAttachment($attachment);
            }
        }
        
        // check to see if client has google account enabled
        if ($this->hasGoogle() && empty($params['system'])) {
            // prepare message for send
            $mail->preSend();
            $mime = $mail->getSentMIMEMessage();

            $message = new \Google_Service_Gmail_Message();
            $message->setRaw(str_replace(array('+','/','='),array('-','_',''),base64_encode($mime)));

            $client = $this->getGoogleClient();
            $gmail = new \Google_Service_Gmail($client);

            $response = $gmail->users_messages->send($this->getUser()->getEmail(), $message);                

            return $response;
        } else {
            $mail->IsSMTP();
            $mail->Host       = "smtp.gmail.com";	//Sets the SMTP server
            $mail->Port       = 465;									//Set the SMTP port for the GMAIL server
            $mail->SMTPDebug  = 2;									//Enables SMTP debug information (for testing)
            $mail->SMTPAuth   = true;								//Enable SMTP authentication
            $mail->SMTPSecure = 'ssl';

            $mail->Username   = "crm@8point3led.co.uk";						//SMTP account username
            $mail->Password   = "zxdfcv45";						//SMTP account password
            //$mail->SetFrom('crm@8point3led.co.uk', '8point3 CRM');
            
            try {
                ob_start();
                if($mail->Send()) {
                    ob_get_clean();
                    return true;
                } else {
                    ob_get_clean();
                    return false;
                }

                
            } catch (Exception $e) {
                ob_get_clean();
                throw $e;
            }
        }
    }
    
    

    public function getConfig() {
        return $this->config;
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

   
    /**
     * get client
     * @return \Client\Entity\Client
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * set client
     * @param \Client\Entity\Client $client
     * @return \Project\Service\DocumentService
     */
    public function setClient(\Client\Entity\Client $client) {
        $this->client = $client;
        return $this;
    }
    
    /**
     * check for client 
     * @return boolean
     */
    public function hasClient() {
        return ($this->client instanceof \Client\Entity\Client);
    }

        
    
    /**
     * get project
     * @return \Project\Entity\Project
     */
    public function getProject() {
        return $this->project;
    }
    

    /**
     * set project
     * @param \Project\Entity\Project $project
     * @return \Project\Service\DocumentService
     */
    public function setProject(\Project\Entity\Project $project) {
        $this->project = $project;
        $this->client = $project->getClient();
        return $this;
    }
    
    
    /**
     * check if project exists
     * @return boolean
     */
    public function hasProject() {
        return ($this->project instanceof \Project\Entity\Project);
    }

    /**
     * get user
     * @return \Application\Entity\User
     */
    public function getUser() {
        return $this->user;
    }
    
     /**
     * check if user exists
     * @return boolean
     */
    public function hasUser() {
        return ($this->user instanceof \Application\Entity\User);
    }

    /**
     * set user
     * @param \Application\Entity\User $user
     * @return \Project\Service\DocumentService
     */
    public function setUser(\Application\Entity\User $user) {
        $this->user = $user;
        return $this;
    }

        
     // factory involkable methods
    function setEntityManager(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }
    
    public function getEntityManager() {
        return $this->em;
    }

    
}

