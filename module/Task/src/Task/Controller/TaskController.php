<?php
namespace Task\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User,    Application\Entity\Address,    Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

use Application\Controller\AuthController;

class TaskController extends AuthController
{
    
    public function indexAction()
    {
        $this->setCaption('Task Manager');

        $form = new \Task\Form\AddTaskForm($this->getEntityManager());
        $form
                ->setAttribute('action', '/task/add/')
                ->setAttribute('class', 'form-horizontal');
        
        $this->getView()->setVariable('form', $form);
        return $this->getView();
    }
    
    public function developmentAction()
    {
        $this->setCaption('Task Manager');

        $form = new \Task\Form\AddTaskForm($this->getEntityManager());
        $form
                ->setAttribute('action', '/task/add/')
                ->setAttribute('class', 'form-horizontal');
        
        $query = $this->getEntityManager()->createQuery("SELECT u.userId, u.forename, u.surname FROM Application\Entity\User u JOIN u.roles r WITH r.id = 10");
        $dTeam = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $defaults = array();
        foreach ($dTeam as $user) {
            $defaults[] = $user['userId'];
        }
        
        $form->get('users')->setValue($defaults);
        $form->get('taskType')->setValue(3);


        
        $this->getView()
                ->setVariable('form', $form)
                ->setVariable('tType', 3);
        return $this->getView();
    }
    
    public function addAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request format');
            }
            
            $post = $this->params()->fromPost();
            
            if (!empty($post['required'])) {
                $required = date_create_from_format('d/m/Y H:i:s', $post['required'].' 23:59:59');
                $post['required'] = $required->format('Y-m-d H:i:s');
            }
            
            $task = new \Task\Entity\Task();
            $task->setClient(null);
            $task->setProject(null);

            $post['taskStatus'] = 1; // initialise status
            $form = new \Task\Form\AddTaskForm($this->getEntityManager());
            $form->bind($task);
            $form->setData($post);
            
            if ($form->isValid()) {
                
                $form->bindValues();
                $task->setUser($this->getUser());
                
                if (!empty($post['projectId'])) {
                    $project = $this->getEntityManager()->find('Project\Entity\Project', $post['projectId']);
                    if (!($project instanceof \Project\Entity\Project)) {
                        throw new \Exception('Project retrieval error');
                    }
                    $task->setProject($project);
                    $task->setClient($project->getClient());
                    
                    $config  = json_decode($task->getTaskType()->getConfig(), true);
                    if (isset($config['state']['pre'])) {
                        $states = array();
                        foreach ($project->getStates() as $state) {
                            $states[$state->getStateId()] = true;
                        }
                        
                        $persist = false;
                        foreach ($config['state']['pre'] as $stateId) {
                            if (empty($states[$stateId])) {
                                $state = $this->getEntityManager()->find('Application\Entity\State', $stateId);
                                $project->getStates()->add($state);
                                $persist = true;
                            }
                        }
                        
                        if ($persist) {
                            $this->getEntityManager()->persist($project);
                        }
                        
                    }
                    
                } elseif (!empty($post['clientId'])) {
                    $client = $this->getEntityManager()->find('Client\Entity\Client', $post['clientId']);
                    if (!($client instanceof \Client\Entity\Client)) {
                        throw new \Exception('Client retrieval error');
                    }
                    $task->setClient($client);
                }
                
                $this->getEntityManager()->persist($task);
                $this->getEntityManager()->flush();
                $data = array('err'=>false, 'task'=>array('taskId'=>$task->getTaskId()));
                
                if (!empty($post['sendEmail'])) {
                    $to = array();
                    $names = array();
                    foreach ($task->getUsers() as $user) {
                        $to[$user->getEmail()] = $user->getEmail();
                        $names[] = $user->getName();
                    }

                    if (!empty($to)) {
                        $uri = $this->getRequest()->getUri();
                        $link = $uri->getScheme().'://'.$uri->getHost().'/task-'.$task->getTaskId().'/';
                        
                        $subject = 'New Task Added - '.$task->getTaskType()->getName();
                        $body = 'A new '.$task->getTaskType()->getName().' task has been added to the system by '.$this->getUser()->getName().' (<a href="mailto: '.$this->getUser()->getEmail().'">'.$this->getUser()->getEmail().'</a>) and assigned to you.<br />'
                                . '<br />'
                                . '<table cellpadding="2" cellspacing="0" border="1">'
                                . '<tbody>'
                                . '<tr><td>Type: </td><td>'.$task->getTaskType()->getName().'</td></tr>'
                                . '<tr><td>Created: </td><td>'.$task->getCreated()->format('l jS \of F Y g:ia').'</td></tr>'
                                . '<tr><td>Created By: </td><td>'.$this->getUser()->getName().'</td></tr>'
                                . '<tr><td>Required Completion Date: </td><td>'.$task->getRequired()->format('l jS \of F Y').'</td></tr>'
                                . '<tr><td>Owners: </td><td>'.implode(', ',$names).'</td></tr>'
                                . '<tr><td>Description: </td><td>'.$task->getDescription().'&nbsp;</td></tr>'
                                . (isset($project)?'<tr><td>Client: </td><td>'.$project->getClient()->getName().'&nbsp;</td></tr>'
                                        . '<tr><td>Project: </td><td>'.$project->getName().'&nbsp;</td></tr>':'')
                                . (isset($project)?'<tr><td>Reference: </td><td><a href="'.$uri->getScheme().'://'.$uri->getHost().'/client-'.$project->getClient()->getClientId().'/project-'.$project->getProjectId().'/">'.
                                        str_pad($project->getClient()->getClientId(), 5, "0", STR_PAD_LEFT).'-'.str_pad($project->getProjectId(), 5, "0", STR_PAD_LEFT).'</a></td></tr>':'')
                                . '</tbody>'
                                . '</table><br /><br />For more information please visit: <a href="'.$link.'">'.$link.'</a><br /><br />';

                        $googleService = $this->getGoogleService();
                        $googleService->sendGmail($subject, $body, $to);
                    }
                    
                }
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }/**/
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?($dropzone?array():array('err'=>true)):$data);/**/
        
    }
    
    public function listAction() {
        try {
            $data = array();
            if (!$this->request->isXmlHttpRequest()) {
                throw new \Exception('illegal request type');
            }
            
            $em = $this->getEntityManager();
            $development = $this->params()->fromQuery('sDevelopment',false);
            $length = $this->params()->fromQuery('iDisplayLength', 10);
            $start = $this->params()->fromQuery('iDisplayStart', 1);
            $keyword = $this->params()->fromQuery('sSearch','');
            $params = array(
                'keyword'=>trim($keyword),
                'orderBy'=>array()
            );

            if ($development) {
                $params['development'] = !empty($development);
            }
            
            $orderBy = array(
                0=>'taskId',
                1=>'taskType',
                3=>'user',
                4=>'required',
                5=>'taskStatus'
            );
            for ( $i=0 ; $i<intval($this->params()->fromQuery('iSortingCols',0)) ; $i++ )
            {
                $j = $this->params()->fromQuery('iSortCol_'.$i);
                if ( $this->params()->fromQuery('bSortable_'.$j, false) == "true" )
                {
                    $dir = $this->params()->fromQuery('sSortDir_'.$i,'ASC');
                    if (isset($orderBy[$j])) {
                        $params['orderBy'][$orderBy[$j]]=$dir;
                    }
                }/**/
            }


            $paginator = $em->getRepository('Task\Entity\Task')->findPaginateByUserId($this->getUser()->getUserId(), $length, $start, $params);

            $data = array(
                "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
                "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
                "iTotalRecords" => $paginator->getcurrentItemCount(),
                "aaData" => array()
            );/**/


            foreach ($paginator as $page) {
                $color = '';
                switch ($page->getTaskStatus()->getTaskStatusId()) {
                    case 1: $color = 'label-info'; break;
                    case 2: $color = ''; break;
                    case 3: $color = 'label-success'; break;
                    case 4: $color = 'label-important'; break;
                }
                //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
                $data['aaData'][] = array (
                    '<a href="/task-'.$page->getTaskId().'">'.str_pad($page->getTaskid(), 5, "0", STR_PAD_LEFT).'</a>',
                    $page->getTaskType()->getName(),
                    $page->getDescription(),
                    $page->getUser()->getName(),
                    $page->getRequired()->format('d/m/Y'),
                    '<span class="label '.$color.'"><i class="'.$page->getTaskStatus()->getIcon().'"></i> '.strtoupper($page->getTaskStatus()->getName()).'</span>',
                    '<a class="btn btn-success action-client-edit" href="/task-'.$page->getTaskId().'" ><i class="icon-edit"></i></a>',
                );
            }    

        } catch (\Exception $ex) {
            $data = array('error'=>true, 'info'=>$ex->getMessage());
        }
        
        return new JsonModel($data);/**/
    }
    
    
    
    function previewAction () {
         try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal message');
            }
            
            $em = $this->getEntityManager();
            $paginator = $em->getRepository('Task\Entity\Task')->findPaginateByUserId($this->getUser()->getUserId(), 5, 1, array (
                'orderBy'=>array ('required'=>'asc'),
                'taskStatus'=>1,
            ));
            $data = array(
                "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
                "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
                "iTotalRecords" => $paginator->getcurrentItemCount(),
                "aaData" => array()
            );/**/
            
            foreach ($paginator as $page) {
                //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
                $info = array (
                    $page->getTaskId(),
                    str_pad($page->getTaskid(), 5, "0", STR_PAD_LEFT),
                    $page->getTaskType()->getName(),
                    $page->getDescription(),
                    $page->getUser()->getName(),
                    $page->getProgress(),
                    $page->getRequired()->format('d/m/Y'),
                    empty($page->getProject())?(empty($page->getClient())?'':str_pad($page->getClient()->getClientId(), 5, "0", STR_PAD_LEFT)):str_pad($page->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT).'-'.str_pad($page->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT),
                );
                
                
                $data['aaData'][] = $info;
            }
            
            $this->getUser()->addConfigProperty('taskCount', $paginator->getTotalItemCount());
            $this->getEntityManager()->persist($this->getUser());
            $this->getEntityManager()->flush();
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
        

    }
     
}
