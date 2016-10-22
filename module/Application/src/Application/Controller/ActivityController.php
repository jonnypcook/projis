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


class ActivityController extends AuthController
{
    
    public function indexAction()
    {
        $this->setCaption('Activity Log');
        /*$formCalendarEvent = new \Application\Form\CalendarEventAddForm();
        $formCalendarEvent 
                ->setAttribute('action', '/calendar/addevent/')
                ->setAttribute('class', 'form-horizontal');

        $this->getView()
                ->setVariable('formCalendarEvent',$formCalendarEvent);/**/
        return $this->getView();
    }
    
    public function listAction() {
        try {
            $data = array();
            if (!$this->request->isXmlHttpRequest()) {
                throw new \Exception('illegal request type');
            }
            
            $em = $this->getEntityManager();
            $length = $this->params()->fromQuery('iDisplayLength', 10);
            $start = $this->params()->fromQuery('iDisplayStart', 1);
            $keyword = $this->params()->fromQuery('sSearch','');
            $params = array(
                'keyword'=>trim($keyword),
                'orderBy'=>array()
            );

            $orderBy = array(
                0=>'startDt',
                1=>'duration',
                2=>'activityType',
                3=>'note',
                4=>'client',
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


            $paginator = $em->getRepository('Application\Entity\Activity')->findPaginateByUserId($this->getUser()->getUserId(), $length, $start, $params);

            $data = array(
                "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
                "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
                "iTotalRecords" => $paginator->getcurrentItemCount(),
                "aaData" => array()
            );/**/


            foreach ($paginator as $page) {
                $link = 'Not Available';
                if (!empty($page->getProject())) {
                    $link= '<a href="/client-'.$page->getProject()->getClient()->getClientId().'/project-'.$page->getProject()->getProjectId().'/">'.str_pad($page->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT).'-'.str_pad($page->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT).'</a>';
                } elseif (!empty($page->getClient())) {
                    $link= '<a href="/client-'.$page->getClient()->getClientId().'/">'.str_pad($page->getClient()->getClientId(), 5, "0", STR_PAD_LEFT).'</a>';
                }
                //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
                $duration = floor(($page->getEndDt()->getTimestamp()-$page->getStartDt()->getTimestamp())/60);
                $data['aaData'][] = array (
                    $page->getStartDt()->format('d/m/Y H:i'),
                    $duration.' Minute'.(($duration==1)?'':'s'),
                    $page->getActivityType()->getName(),
                    $page->getNote(),
                    $link,
                );
            }    

        } catch (\Exception $ex) {
            $data = array('error'=>true, 'info'=>$ex->getMessage());
        }
        
        return new JsonModel($data);/**/
    }
    
    public function listClientAction() {
        try {
            if (!$this->request->isXmlHttpRequest()) {
                throw new \Exception('illegal request type');
            }
            
            $em = $this->getEntityManager();
            $length = $this->params()->fromQuery('iDisplayLength', 10);
            $start = $this->params()->fromQuery('iDisplayStart', 1);
            $keyword = $this->params()->fromQuery('sSearch','');
            
            $clientId = $this->params()->fromQuery('clientId',false);
            if (empty($clientId)) {
                throw new \Exception('client identifier not found');
            }
            
            $projectId = $this->params()->fromQuery('projectId',false);
            $params = array(
                'keyword'=>trim($keyword),
                'orderBy'=>array()
            );


            $orderBy = array(
                0=>'startDt',
                1=>'type',
                2=>'user',
                3=>'note',
                4=>'duration',
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

            if (!empty($projectId)) {
                $params['projectId'] = $projectId;
            }     
            
            $paginator = $em->getRepository('Application\Entity\Activity')->findPaginateByClientId($clientId, $length, $start, $params);

            $data = array(
                "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
                "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
                "iTotalRecords" => $paginator->getcurrentItemCount(),
                "aaData" => array()
            );/**/

            foreach ($paginator as $page) {
                $duration = floor(($page->getEndDt()->getTimestamp()-$page->getStartDt()->getTimestamp())/60);
                $notes = $page->getNote();
                if (empty($projectId)) {
                    if ($page->getProject() instanceof \Project\Entity\Project) {
                        $notes = $page->getNote().' <a class="pull-right" href="/client-'.$clientId.'/project-'.$page->getProject()->getProjectId().'/"><i class="icon-double-angle-right"></i></a>';
                    }
                }
                $data['aaData'][] = array (
                    $page->getStartDt()->format('d/m/Y H:i'),
                    $page->getActivityType()->getName(),
                    $page->getUser()->getName(),
                    $notes,
                    $duration.' Minute'.(($duration==1)?'':'s'),
                );
            } 
                  
        } catch (\Exception $ex) {
            $data=array($ex->getMessage());
        }
        
        return new JsonModel($data);/**/
    }
    
    public function addAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new \Exception('illegal message');
        }

        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('illegal method');
            }

            $post = $this->getRequest()->getPost();
            
            $info=array();
            $errs = array();
            $args = array();
            
            // check message
            if (empty($post['note'])) {
                $errs['note'] = 'no note supplied';
            }
            
            // check type
            $activityType = $this->getEntityManager()->find('Application\Entity\ActivityType', empty($post['activityTypeId'])?500:$post['activityTypeId']);
            if (empty($activityType)) {
                $errs['activityTypeId'] = 'Illegal Activity Id';
            } else { // duration config
                if (!empty($post['startDt']) && !empty($post['startTm'])) {
                    $dtTmStr = $post['startDt'].' '.$post['startTm'];
                    $args['startDt'] = date_create_from_format('d/m/Y H:i', $dtTmStr);
                    
                    if (!empty($post['endDt']) && !empty($post['endTm'])) {
                        $dtTmStr = $post['endDt'].' '.$post['endTm'];
                        $args['endDt'] = date_create_from_format('d/m/Y H:i', $dtTmStr);
                    } else {
                        if (!isset($post['duration'])) {
                            $duration = $activityType->getMins();
                        } else {
                            $duration = (int)$post['duration'];
                        }
                        $date = new \DateTime();
                        $args['endDt'] = $date->setTimestamp($args['startDt']->getTimestamp()+($duration*60)); 
                    }
                    
                } else {
                    $args['startDt'] = new \DateTime();
                    if (!isset($post['duration'])) {
                        $duration = $activityType->getMins();
                    } else {
                        $duration = (int)$post['duration'];
                    }
                    
                    $date = new \DateTime();
                    $args['endDt'] = $date->setTimestamp($args['startDt']->getTimestamp()+($duration*60)); 
                }
                

            }
            
            // check for project/client specific ownership
            if (!empty($post['projectId'])) {
                $project = $this->getEntityManager()->find('Project\Entity\Project', $post['projectId']);
                if (empty($project)) {
                    $errs['projectId'] = 'Illegal Project Id';
                }
                $args['project']=$project->getProjectId();
                $args['client']=$project->getClient()->getClientId();
            } elseif (!empty($post['clientId'])) {
                $client = $this->getEntityManager()->find('Client\Entity\Client', $post['clientId']);
                if (empty($client)) {
                    $errs['clientId'] = 'Illegal Client Id';
                }
                $args['client']=$client->getClientId();
            } 
            
            if (empty($errs)) {
                $activity = $this->AuditPlugin()->activity($activityType->getActivityTypeId(), $this->getUser()->getUserId(), $post['note'], $args);
                $picture = $activity->getUser()->getPicture();
                $info['activity'] = array (
                    'id' => $activity->getActivityId(),
                    'type' => $activity->getActivityType()->getName(),
                    'start' => $activity->getStartDt()->format('g:ia, jS F Y'),
                    'end' => $activity->getEndDt()->format('g:ia, jS F Y'),
                    'duration' => ($activity->getEndDt()->getTimestamp()-$activity->getStartDt()->getTimestamp())/60,
                    'note' => $activity->getNote(),
                    'user' => ucwords($activity->getUser()->getForename().' '.$activity->getUser()->getSurname()),
                    'me' => ($activity->getUser()->getUserId()==$this->getUser()->getUserId())?true:false,
                    'picture'=>empty($picture)?'default':$picture,
                );
                
                $prj = $activity->getProject();
                $clt = $activity->getClient();
                if (!empty($prj)) {
                    $info['activity']['projectName']=$prj->getName();
                    $info['activity']['projectId']=$prj->getProjectId();
                    $info['activity']['clientId']=$prj->getClient()->getClientId();
                } elseif (!empty($clt)) {
                    $info['activity']['clientName']=$clt->getName();
                    $info['activity']['clientId']=$clt->getClientId();
                }
                
                $data = array('err'=>false, 'info'=>$info);
            } else {
                $data = array('err'=>true, 'info'=>$errs);
            }

            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    
    
}
