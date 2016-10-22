<?php
namespace Task\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User,    Application\Entity\Address,    Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Application\Controller\AuthController;

class TaskitemController extends AuthController
{
    
    /**
     *
     * @var Task\Entity\Task
     */
    private $task;
    
    public function getTask() {
        return $this->task;
    }

    public function setTask(\Task\Entity\Task $task) {
        $this->task = $task;
        $this->getView()->setVariable('task', $task);
        return $this;
    }

        
    public function onDispatch(MvcEvent $e) {
        $tid = (int) $this->params()->fromRoute('tid', 0);

        if (empty($tid)) {
            return $this->redirect()->toRoute('task');
        } 
        
        $task = $this->getEntityManager()->find('Task\Entity\Task', $tid);

        if (!($task instanceof \Task\Entity\Task)) {
            return $this->redirect()->toRoute('task');
        }
        
        if (!$this->isGranted('admin.all')) {
            // check to see if user can view this item
            if ($task->getUser()->getUserId() != $this->identity()->getUserId()) {
                $passed = false;
                foreach ($task->getUsers() as $user) {
                    if ($user->getUserId() == $this->identity()->getUserId()) {
                        $passed = true;
                        break;
                    }
                }
                if (!$passed) {
                    return $this->redirect()->toRoute('tasks');
                }
            }
        }
        
        $this->setTask($task);
        
        $this->amendNavigation();
        
        return parent::onDispatch($e);
    }
    
    public function indexAction()
    {
        $em = $this->getEntityManager();
        $duration = 0;
        foreach ($this->getTask()->getActivities() as $activity) {
            $duration+= ($activity->getEndDt()->getTimestamp() - $activity->getStartDt()->getTimestamp());
        }
        $this->setCaption('Task Item: #'.str_pad($this->getTask()->getTaskId(), 5, "0", STR_PAD_LEFT));
        
        $formAddActivityNote = new \Task\Form\AddTaskActivityForm();
        $formAddActivityNote
                //->setAttribute('class', 'form-horizontal')
                ->setAttribute('action', '/task-'.$this->getTask()->getTaskId().'/addactivity/');
        
        $formOwner = new \Task\Form\ChangeOwnerForm($em);
        $formOwner
                ->setAttribute('class', 'form-horizontal')
                ->setAttribute('action', '/task-'.$this->getTask()->getTaskId().'/owners/');
        $formOwner->bind($this->getTask());
        
        $formDeliveryDate = new \Task\Form\ChangeDeliveryForm();
        $formDeliveryDate
                ->setAttribute('class', 'form-horizontal')
                ->setAttribute('action', '/task-'.$this->getTask()->getTaskId().'/deliverydate/');
        $formDeliveryDate->get('required')->setValue($this->getTask()->getRequired()->format('d/m/Y'));
        
        
        $this->getView()
                ->setVariable('formDeliveryDate', $formDeliveryDate)
                ->setVariable('formOwner', $formOwner)
                ->setVariable('duration', $duration)
                ->setVariable('formAddActivityNote', $formAddActivityNote);
        
        return $this->getView();
    }
    
    public function addActivityAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $post = $this->params()->fromPost();
            
            $activity = new \Application\Entity\Activity();

            $form = new \Task\Form\AddTaskActivityForm();
            $form->setInputFilter(new \Task\Form\AddTaskActivityFilter());
            
            // add additional items
            $form->setData($post);
            
            if ($form->isValid()) {
                $post['activityType'] = 20;
                $hydrator = new DoctrineHydrator($this->getEntityManager(),'Application\Entity\Activity');
                $hydrator->hydrate($post, $activity);
                
                $startDt = new \DateTime();
                $date = new \DateTime();
                $endDt = $date->setTimestamp($startDt->getTimestamp()+($form->get('duration')->getValue()*60)); 
                
                $activity
                    ->setStartDt($startDt)
                    ->setEndDt($endDt)
                    ->setUser($this->getUser())
                    ->setClient($this->getTask()->getClient())
                    ->setProject($this->getTask()->getProject())
                ;/**/
                
                $this->getEntityManager()->persist($activity);
                
                $this->getTask()->getActivities()->add($activity);
                $this->getEntityManager()->persist($this->getTask());
                
                $this->getEntityManager()->flush();
                
                $data = array('err'=>false);
                
                $this->flashMessenger()->addMessage(array(
                    'The task activity has been added successfully.', 'Success!'
                ));
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }/**/
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function ownersAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $em = $this->getEntityManager();
            $post = $this->params()->fromPost();
            $form = new \Task\Form\ChangeOwnerForm($em);
            //$form->bind($this->getTask());
            
            
            $form->setData($post);
            
            if ($form->isValid()) {
                $hydrator = new DoctrineHydrator($this->getEntityManager(),'Task\Entity\Task');
                $hydrator->hydrate($post, $this->getTask());

                $em->persist($this->getTask());
                $em->flush();
                
                $this->flashMessenger()->addMessage(array(
                    'The task owners have been updated successfully.', 'Success!'
                ));
        
                $data = array('err'=>false);
            
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }/**/
            
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function deliveryDateAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $em = $this->getEntityManager();
            $post = $this->params()->fromPost();
            $form = new \Task\Form\ChangeDeliveryForm();
            //$form->bind($this->getTask());
            
            if (!empty($post['required'])) {
                $required = date_create_from_format('d/m/Y H:i:s', $post['required'].' 23:59:59');
                $post['required'] = $required->format('Y-m-d H:i:s');
            }
            $form->setData($post);
            
            if ($form->isValid()) {
                $this->getTask()->setRequired($required);

                $em->persist($this->getTask());
                $em->flush();
                
                $this->flashMessenger()->addMessage(array(
                    'The task delivery date has been updated successfully.', 'Success!'
                ));
        
                $data = array('err'=>false);
            
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }/**/
            
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function settingsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $post = $this->params()->fromPost();
            
            if (isset($post['progress'])) {
                if (preg_match ('/^[\d]+$/', $post['progress'])) {
                    if (($post['progress']>=0) && ($post['progress']<=100)) {
                        $this->getTask()->setProgress($post['progress']);
                        $this->getEntityManager()->persist($this->getTask());
                        $this->getEntityManager()->flush();
                    }
                }
            }
            $data = array('err'=>false);

            $this->flashMessenger()->addMessage(array(
                'The task settings have been updated successfully.', 'Success!'
            ));
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function completeAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            $activity = new \Application\Entity\Activity();
            $post['activityType'] = 21; // task completed
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Application\Entity\Activity');
            $hydrator->hydrate($post, $activity);
                
            $date = new \DateTime();
                
            $activity
                ->setStartDt($date)
                ->setEndDt($date)
                ->setUser($this->getUser())
                ->setClient($this->getTask()->getClient())
                ->setProject($this->getTask()->getProject())
            ;/**/
            
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Task\Entity\Task');
            $hydrator->hydrate(array('taskStatus'=>3, 'progress'=>100), $this->getTask());
            
            $this->getTask()->setCompleted(new \DateTime());
            $this->getEntityManager()->persist($activity);
            $this->getTask()->getActivities()->add($activity);
            $this->getEntityManager()->persist($this->getTask());
            
            $config  = json_decode($this->getTask()->getTaskType()->getConfig(), true);
            if (isset($config['state']['post']) && ($this->getTask()->getProject() instanceof \Project\Entity\Project)) {
                $states = array();
                foreach ($this->getTask()->getProject()->getStates() as $state) {
                    $states[$state->getStateId()] = true;
                }

                $persist = false;
                foreach ($config['state']['post'] as $stateId) {
                    if (empty($states[$stateId])) {
                        $state = $this->getEntityManager()->find('Application\Entity\State', $stateId);
                        $this->getTask()->getProject()->getStates()->add($state);
                        $persist = true;
                    }
                }

                if ($persist) {
                    $this->getEntityManager()->persist($this->getTask()->getProject());
                }

            }            
            
            

            $this->getEntityManager()->flush();
            
            $fromEmail = $this->params()->fromPost('sendEmail', false);
            if (!empty($fromEmail)) {
                $email = $this->getTask()->getUser()->getEmail();
                if (!empty($email)) {
                    $to = array($email);
                    $uri = $this->getRequest()->getUri();
                    $link = $uri->getScheme().'://'.$uri->getHost().'/task-'.$this->getTask()->getTaskId().'/';

                    $subject = 'Task Completed - '.$this->getTask()->getTaskType()->getName();
                    $body = 'A '.$this->getTask()->getTaskType()->getName().' task has been completed by '.$this->getUser()->getName().' (<a href="mailto: '.$this->getUser()->getEmail().'">'.$this->getUser()->getEmail().'</a>).<br />'
                            . '<br />'
                            . '<table cellpadding="2" cellspacing="0" border="1">'
                            . '<tbody>'
                            . '<tr><td>Type: </td><td>'.$this->getTask()->getTaskType()->getName().'</td></tr>'
                            . '<tr><td>Created: </td><td>'.$this->getTask()->getCreated()->format('l jS \of F Y g:ia').'</td></tr>'
                            . '<tr><td>Created By: </td><td>'.$this->getTask()->getUser()->getName().'</td></tr>'
                            . '<tr><td>Required Completion Date: </td><td>'.$this->getTask()->getRequired()->format('l jS \of F Y').'</td></tr>'
                            . '<tr><td>Completed: </td><td>'.$this->getTask()->getCompleted()->format('l jS \of F Y g:ia').'</td></tr>'
                            . '<tr><td>Completed By: </td><td>'.$this->getUser()->getName().'</td></tr>'
                            . '<tr><td>Description: </td><td>'.$this->getTask()->getDescription().'&nbsp;</td></tr>'
                            . (($this->getTask()->getProject() instanceof \Project\Entity\Project)?'<tr><td>Client: </td><td>'.$this->getTask()->getProject()->getClient()->getName().'&nbsp;</td></tr>'
                                    . '<tr><td>Project: </td><td>'.$this->getTask()->getProject()->getName().'&nbsp;</td></tr>':'')
                            . (($this->getTask()->getProject() instanceof \Project\Entity\Project)?'<tr><td>Reference: </td><td><a href="'.$uri->getScheme().'://'.$uri->getHost().'/client-'.$this->getTask()->getProject()->getClient()->getClientId().'/project-'.$this->getTask()->getProject()->getProjectId().'/">'.
                                    str_pad($this->getTask()->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT).'-'.str_pad($this->getTask()->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT).'</a></td></tr>':'')
                            . '</tbody>'
                            . '</table><br /><br />For more information please visit: <a href="'.$link.'">'.$link.'</a><br /><br />';

                    $googleService = $this->getGoogleService();
                    $googleService->sendGmail($subject, $body, $to);
                }

            }

            $data = array('err'=>false);

            $this->flashMessenger()->addMessage(array(
                'The task has been marked as completed.', 'Success!'
            ));
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function cancelAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $activity = new \Application\Entity\Activity();
            $post['activityType'] = 22; // task cancelled
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Application\Entity\Activity');
            $hydrator->hydrate($post, $activity);
                
            $date = new \DateTime();
                
            $activity
                ->setStartDt($date)
                ->setEndDt($date)
                ->setUser($this->getUser())
                ->setClient($this->getTask()->getClient())
                ->setProject($this->getTask()->getProject())
            ;/**/
            
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Task\Entity\Task');
            $hydrator->hydrate(array('taskStatus'=>4), $this->getTask());
            
                
            $this->getEntityManager()->persist($activity);
            $this->getTask()->getActivities()->add($activity);
            $this->getEntityManager()->persist($this->getTask());

            $this->getEntityManager()->flush();

            $data = array('err'=>false);

            $this->flashMessenger()->addMessage(array(
                'The task has been marked as cancelled.', 'Success!'
            ));
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function reminderAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $task = $this->getTask();
            
            $to = array();
            $names = array();
            foreach ($task->getUsers() as $user) {
                $to[$user->getEmail()] = $user->getEmail();
                $names[] = $user->getName();
            }

            if (!empty($to)) {
                $uri = $this->getRequest()->getUri();
                $link = $uri->getScheme().'://'.$uri->getHost().'/task-'.$task->getTaskId().'/';

                $subject = 'Task Reminer - '.$task->getTaskType()->getName();
                $body = 'You have a '.$task->getTaskType()->getName().' task active on the system created by '.$this->getUser()->getName().' (<a href="mailto: '.$this->getUser()->getEmail().'">'.$this->getUser()->getEmail().'</a>) and assigned to you.<br />'
                        . '<br />'
                        . '<table cellpadding="2" cellspacing="0" border="1">'
                        . '<tbody>'
                        . '<tr><td>Type: </td><td>'.$task->getTaskType()->getName().'</td></tr>'
                        . '<tr><td>Created: </td><td>'.$task->getCreated()->format('l jS \of F Y g:ia').'</td></tr>'
                        . '<tr><td>Created By: </td><td>'.$this->getUser()->getName().'</td></tr>'
                        . '<tr><td>Required Completion Date: </td><td>'.$task->getRequired()->format('l jS \of F Y').'</td></tr>'
                        . '<tr><td>Owners: </td><td>'.implode(', ',$names).'</td></tr>'
                        . '<tr><td>Description: </td><td>'.$task->getDescription().'&nbsp;</td></tr>'
                        . '</tbody>'
                        . '</table><br /><br />For more information please visit: <a href="'.$link.'">'.$link.'</a><br /><br />';

                $googleService = $this->getGoogleService();
                $googleService->sendGmail($subject, $body, $to);
            }
            
            $data = array('err'=>false);
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function suspendAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $activity = new \Application\Entity\Activity();
            $post['activityType'] = 24; // task re-enabled
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Application\Entity\Activity');
            $hydrator->hydrate($post, $activity);
                
            $date = new \DateTime();
                
            $activity
                ->setStartDt($date)
                ->setEndDt($date)
                ->setUser($this->getUser())
                ->setClient($this->getTask()->getClient())
                ->setProject($this->getTask()->getProject())
            ;/**/
            
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Task\Entity\Task');
            $hydrator->hydrate(array('taskStatus'=>2), $this->getTask());
            
                
            $this->getEntityManager()->persist($activity);
            $this->getTask()->getActivities()->add($activity);
            $this->getEntityManager()->persist($this->getTask());

            $this->getEntityManager()->flush();

            $data = array('err'=>false);

            $this->flashMessenger()->addMessage(array(
                'The task has been marked as suspended.', 'Success!'
            ));
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function reenableAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $activity = new \Application\Entity\Activity();
            $post['activityType'] = 23; // task re-enabled
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Application\Entity\Activity');
            $hydrator->hydrate($post, $activity);
                
            $date = new \DateTime();
                
            $activity
                ->setStartDt($date)
                ->setEndDt($date)
                ->setUser($this->getUser())
                ->setClient($this->getTask()->getClient())
                ->setProject($this->getTask()->getProject())
            ;/**/
            
            $hydrator = new DoctrineHydrator($this->getEntityManager(),'Task\Entity\Task');
            $hydrator->hydrate(array('taskStatus'=>1), $this->getTask());
            
                
            $this->getEntityManager()->persist($activity);
            $this->getTask()->getActivities()->add($activity);
            $this->getEntityManager()->persist($this->getTask());

            $this->getEntityManager()->flush();

            $data = array('err'=>false);

            $this->flashMessenger()->addMessage(array(
                'The task has been marked as active.', 'Success!'
            ));
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    
    public function amendNavigation() {
        // check current location
        $action = $this->params('action');
        
        // get client
        $task = $this->getTask();
        
        // grab navigation object
        $navigation = $this->getServiceLocator()->get('navigation');

        $navigation->addPage(array(
            'permissions' => array('task.read'),
            'type' => 'uri',
            'active'=>true,  
            'ico'=> 'icon-tasks',
            'order'=>1,
            'route' => 'tasks',
            'uri'=> '/task/',
            'label' => 'Tasks',
            'skip' => true,
            'pages' => array(
                array (
                    'type' => 'uri',
                    'active'=>true,  
                    'ico'=> 'icon-tasks',
                    'order'=>1,
                    'uri'=> '/task-'.$task->getTaskId().'/',
                    'label' => 'Task Item #'.str_pad($task->getTaskId(), 5, "0", STR_PAD_LEFT),
                    'mlabel' => 'Task #'.str_pad($task->getTaskId(), 5, "0", STR_PAD_LEFT),
                )
            )
        ));
        
     
    }
    
     
}
