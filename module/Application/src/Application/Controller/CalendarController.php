<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User,    Application\Entity\Address,    Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;


class CalendarController extends AuthController
{
    
    private $calendarUser;
    
    /**
     * get calengar user
     * @return \Application\Entity\User
     */
    public function getCalendarUser() {
        return $this->calendarUser;
    }

    public function setCalendarUser($calendarUser) {
        $this->calendarUser = $calendarUser;
        $this->getView()->setVariable('calendarUser', $this->calendarUser);
        return $this;
    }
    
    private $eventAware = false;
    
    public function getEventAware() {
        return (bool)$this->eventAware;
    }

    public function setEventAware($eventAware) {
        $this->eventAware = (bool)$eventAware;
        $this->getView()->setVariable('eventaware', $eventAware);
        return $this;
    }

        
        
    public function onDispatch(MvcEvent $e) {
        if ($this->isGranted('calendar.share')) {
            $userId = $this->params()->fromQuery('userId', false);
            if (!empty($userId)) {
                if ($userId != $this->identity()->getUserId()) {
                    $user = $this->getEntityManager()->find('Application\Entity\User', $userId);
                    if ($user instanceof \Application\Entity\User) {
                        if ($user->getCompany()->getCompanyId() == $this->identity()->getCompany()->getCompanyId()) {
                            if ($user->getGoogleEnabled()) {
                                $this->setCalendarUser($user);
                                $this->setEventAware(false);
                            }
                        }
                    }
                }

            }
        }
        
        if (!($this->getCalendarUser() instanceof \Application\Entity\User)) {
            $this->setCalendarUser($this->identity());
            $this->setEventAware(true);
        } 
        
        return parent::onDispatch($e);
    }
    public function indexAction()
    {
        if ($this->isGranted('calendar.share')) {
            $users = $this->getEntityManager()->getRepository('Application\Entity\User')->findByCompany($this->getUser()->getCompany()->getCompanyId(), array('gAware'=>true, 'exclude'=>array($this->getUser()->getUserId())));
            $this->getView()->setVariable('users', $users);
        }
        
        $this->setCaption('Calendar');
        $formCalendarEvent = new \Application\Form\CalendarEventAdvancedAddForm($this->getEntityManager(), array('companyId'=>$this->getUser()->getCompany()->getCompanyId()));
        $formCalendarEvent 
                ->setAttribute('action', '/calendar/addevent/')
                ->setAttribute('class', 'form-horizontal');

        $this->getView()
                ->setVariable('formCalendarEvent',$formCalendarEvent);
        return $this->getView();
    }
    
    public function addEventAction() {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }

            $googleService = $this->getGoogleService();
            
            if (!$googleService->hasGoogle()) {
                throw new \Exception ('the service is not enabled for this user');
            }
            
            
            $postData = $this->params()->fromPost();
            $formCalendarEvent = new \Application\Form\CalendarEventAdvancedAddForm($this->getEntityManager(), array('companyId'=>$this->getUser()->getCompany()->getCompanyId()));
            $formCalendarEvent->setInputFilter(new \Application\Form\CalendarEventAddFilter());
            
            $config = array();
            if (!empty($postData['users'])) {
                $config['attendees'] = $postData['users'];
                unset($postData['users']);
            }

            $formCalendarEvent->setData($postData);
            if ($formCalendarEvent->isValid()) {
                if (!empty($postData['projectId'])) {
                    $project = $this->getEntityManager()->find('Project\Entity\Project', $postData['projectId']);
                    if(empty($project)) {
                        throw new \Exception('Project could not be found');
                    }
                    
                    $googleService->setProject($project);
                }
                
                if(!empty($formCalendarEvent->get('sendNotifications')->getValue())) {
                    $config['notifications'] = true;
                }
            
                $eventId = $this->params()->fromQuery('eid', false);
                if (!empty($eventId)) {
                    $event = $googleService->findCalendarEvent($eventId);
                    if (!($event instanceof \Google_Service_Calendar_Event)) {
                        throw new \Exception('illegal event supplied');
                    }
                    $config['event'] = $event;
                }
                
                // check and add attendees
                if (empty($config['attendees'])) {
                    $config['attendees'] = array();
                }
                if (!empty($formCalendarEvent->get('usersBespoke')->getValue())) {
                    foreach (explode(',',$formCalendarEvent->get('usersBespoke')->getValue()) as $email) {
                        if (!in_array($email, $config['attendees'])) {
                            $config['attendees'][$email]= $email;
                        }
                    }
                }
                
                
                // event location
                if (!empty($formCalendarEvent->get('location')->getValue())) {
                    $config['location'] = $formCalendarEvent->get('location')->getValue();
                }
                
                if (!empty($formCalendarEvent->get('description')->getValue())) {
                    $config['description'] = $formCalendarEvent->get('description')->getValue();
                }
                
                // event timings
                if (empty($formCalendarEvent->get('calStartTm')->getValue()) || !empty($formCalendarEvent->get('allday')->getValue())) {
                    $config['allday'] = true;
                    $dateStart = \DateTime::createFromFormat('d/m/Y', $formCalendarEvent->get('calStartDt')->getValue());
                    $dateEnd = \DateTime::createFromFormat('d/m/Y', $formCalendarEvent->get('calEndDt')->getValue());
                } else {
                    $dateStart = \DateTime::createFromFormat('d/m/Y H:i', $formCalendarEvent->get('calStartDt')->getValue().' '.$formCalendarEvent->get('calStartTm')->getValue());
                    $dateEnd = \DateTime::createFromFormat('d/m/Y H:i', $formCalendarEvent->get('calEndDt')->getValue().' '.$formCalendarEvent->get('calEndTm')->getValue());
                }
                
                //$this->debug()->dump($postData);

                $data = $googleService->addCalendarEvent($formCalendarEvent->get('title')->getValue(), $dateStart->getTimestamp(), $dateEnd->getTimestamp(), $config);
                
                if (empty($postData['nogrowl'])) {
                    $this->flashMessenger()->addMessage(array('The calendar event has been '.(!empty($eventId)?'updated':'added').' successfully', 'Success!'));
                }
                
                if ($googleService->hasProject()) {
                    $this->AuditPlugin()->activityProject(4, $this->getUser()->getUserId(), $googleService->getProject()->getClient()->getClientId(), $googleService->getProject()->getProjectId(), 
                        $formCalendarEvent->get('title')->getValue(), array (
                        'start'=>$dateStart,
                        'duration'=>(($dateEnd->getTimestamp() - $dateStart->getTimestamp())/60),
                        'data'=>$data,
                    ));/**/
                    
                }
                
            } else {
                $data = array('err'=>true, 'info'=>$formCalendarEvent->getMessages());
            }
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel($data);/**/
    
        
    }
    
    public function advancedEventAction() {
        $this->setCaption('Advanced Event');
        $formCalendarEvent = new \Application\Form\CalendarEventAdvancedAddForm($this->getEntityManager(), array('companyId'=>$this->getUser()->getCompany()->getCompanyId()));
        $formCalendarEvent 
                ->setAttribute('action', '/calendar/addevent/')
                ->setAttribute('class', 'form-horizontal');
        
        $recipients = array();
        $users = $this->getEntityManager()->getRepository('Application\Entity\User')->findByCompany($this->getUser()->getCompany()->getCompanyId());
        foreach ($users as $user) {
            if (empty($user->getEmail())) {
                continue;
            }
            if (!empty($recipients[$user->getEmail()])) { // cannot have duplicate email addresses
                continue;
            }
            $recipients[$user->getEmail()] = $user->getName();
        }   
        
        $formCalendarEvent->get('users')->setAttribute('options', $recipients);
        
        // now set defaults
        $params = $this->params()->fromQuery();
        if (!empty($params['title'])) {
            $formCalendarEvent->get('title')->setValue($params['title']);
        }

        if (empty($params['calEndTm'])) {
            $dateStart = \DateTime::createFromFormat('d/m/Y', $params['calStartDt']);
            $dateEnd = \DateTime::createFromFormat('d/m/Y', $params['calEndDt']);
            $formCalendarEvent->get('allday')->setValue(true);
            $formCalendarEvent->get('calStartDt')->setValue($dateStart->format('d/m/Y'));
            $formCalendarEvent->get('calEndDt')->setValue($dateEnd->format('d/m/Y'));
            $formCalendarEvent->get('calStartTm')->setValue('09:00')->setAttribute('disabled', true);
            $formCalendarEvent->get('calEndTm')->setValue('17:30')->setAttribute('disabled', true);
        } else {
            $dateStart = \DateTime::createFromFormat('d/m/Y H:i', $params['calStartDt'].' '.$params['calStartTm']);
            $dateEnd = \DateTime::createFromFormat('d/m/Y H:i', $params['calEndDt'].' '.$params['calEndTm']);
            
            
            $formCalendarEvent->get('calStartDt')->setValue($dateStart->format('d/m/Y'));
            $formCalendarEvent->get('calStartTm')->setValue($dateStart->format('H:i'));
            $formCalendarEvent->get('calEndDt')->setValue($dateEnd->format('d/m/Y'));
            $formCalendarEvent->get('calEndTm')->setValue($dateEnd->format('H:i'));
        }
        $this->getView()
                ->setVariable('creatable', true)
                ->setVariable('formCalendarEvent',$formCalendarEvent);
        
        
        return $this->getView();
    }
    
    
    /**
     * main Dashboard action
     * @return \Zend\View\Model\ViewModel
     */
    public function eventListAction()
    {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            $start = $this->params()->fromQuery('start', false);
            $end = $this->params()->fromQuery('end', false);
        
            if (empty($start) || empty($end)) {
                throw new \Exception('missing parameters');
            }
            
            $googleService = $this->getGoogleService();
            
            if (!$googleService->hasGoogle()) {
                throw new \Exception ('the service is not enabled for this user');
            }

            $data = $googleService->findCalendarEvents(array (
                'start' => strtotime($start),
                'end' => strtotime($end),
                'owner' => $this->getCalendarUser()->getEmail(),
                'linkable' => $this->getEventAware(),
            ));
            
        
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel($data);/**/
    }
    
    public function advancedEditAction() {
        try
        {
            $eventId = $this->params()->fromQuery('eid', false);
            
            if (empty($eventId)) {
                throw new \Exception('no event specified');
            }
            $googleService = $this->getGoogleService();
            $event = $googleService->findCalendarEvent($eventId);
            
            //$this->debug()->dump($event);
            
            $editMode = ($event->getCreator()->getEmail()==$this->getUser()->getEmail());
            $cancelled = ($event->getStatus()=='cancelled'); 
        
            
            $formCalendarEvent = new \Application\Form\CalendarEventAdvancedAddForm($this->getEntityManager(), array('companyId'=>$this->getUser()->getCompany()->getCompanyId()));
            $formCalendarEvent 
                    ->setAttribute('action', '/calendar/addevent/?eid='.$eventId)
                    ->setAttribute('class', 'form-horizontal');

            $emails = array();
            if (!empty($event->getAttendees())) {
                foreach ($event->getAttendees() as $attendee) {
                    $emails[strtolower($attendee->getEmail())] = $attendee->getEmail();
                }
            }
            $recipientsVals = array();
            $recipients = array();
            $users = $this->getEntityManager()->getRepository('Application\Entity\User')->findByCompany($this->getUser()->getCompany()->getCompanyId());
            foreach ($users as $user) {
                if (empty($user->getEmail())) {
                    continue;
                }
                if (!empty($recipients[$user->getEmail()])) { // cannot have duplicate email addresses
                    continue;
                }

                $recipients[$user->getEmail()] = $user->getName();
                
                if (!empty($emails[strtolower($user->getEmail())])) {
                    $recipientsVals[]=$user->getEmail();
                    unset($emails[strtolower($user->getEmail())]);
                }
            }   

            //$this->debug()->dump(strtotime($event->getstart()->getdatetime()));
            $formCalendarEvent->get('users')
                    ->setAttribute('options', $recipients)
                    ->setValue($recipientsVals);
            
            if (!empty($emails)) {
                $formCalendarEvent->get('usersBespoke')->setValue(implode(',', array_keys($emails)));
            }
            
            $formCalendarEvent->get('title')->setValue($event->getSummary());
            $formCalendarEvent->get('location')->setValue($event->getLocation());
            $formCalendarEvent->get('description')->setValue($event->getDescription());
            
            if (!empty($event->getstart()->getdate())) { // all day
                $dateStart = new \DateTime();
                $dateStart->setTimestamp(strtotime($event->getstart()->getdate()));
                $dateEnd = new \DateTime();
                $dateEnd->setTimestamp(strtotime($event->getend()->getdate()));
                
                $formCalendarEvent->get('allday')->setValue(true);
                $formCalendarEvent->get('calStartDt')->setValue($dateStart->format('d/m/Y'));
                $formCalendarEvent->get('calEndDt')->setValue($dateEnd->format('d/m/Y'));
                $formCalendarEvent->get('calStartTm')->setValue('09:00')->setAttribute('disabled', true);
                $formCalendarEvent->get('calEndTm')->setValue('17:30')->setAttribute('disabled', true);
            } else { 
                $dateStart = new \DateTime();
                $dateStart->setTimestamp(strtotime($event->getstart()->getdatetime()));
                $dateEnd = new \DateTime();
                $dateEnd->setTimestamp(strtotime($event->getend()->getdatetime()));

                $formCalendarEvent->get('calStartDt')->setValue($dateStart->format('d/m/Y'));
                $formCalendarEvent->get('calStartTm')->setValue($dateStart->format('H:i'));
                $formCalendarEvent->get('calEndDt')->setValue($dateEnd->format('d/m/Y'));
                $formCalendarEvent->get('calEndTm')->setValue($dateEnd->format('H:i'));                
            }

           
            $this->getView()
                    ->setVariable('eventId', $eventId)
                    ->setVariable('event', $event)
                    ->setVariable('cancelled', $cancelled)
                    ->setVariable('editable', $editMode)
                    ->setVariable('formCalendarEvent',$formCalendarEvent);
            
            return $this->getView();
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('calendar');
        }
    }
    
    public function deleteeventAction() {
        try
        {
            $eventId = $this->params()->fromPost('eid', false);
            
            if (empty($eventId)) {
                throw new \Exception('no event specified');
            }
            
            $googleService = $this->getGoogleService();
            
            $event = $googleService->deleteCalendarEvent($eventId);
            $this->flashMessenger()->addMessage(array('The calendar event has been deleted successfully', 'Success!'));
            
            $data = array('err'=>false, 'info'=>$event);

        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel($data);/**/
    }
    
    
}
