<?php
namespace Space\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Controller\AuthController;

use Zend\Mvc\MvcEvent;


class SpaceSpecificController extends AuthController
{
    /**
     *
     * @var Project\Entity\Project
     */
    private $project;
    
    /**
     *
     * @var Space\Entity\Space 
     */
    private $space;
    
    public function onDispatch(MvcEvent $e) {
        $cid = (int) $this->params()->fromRoute('cid', 0);
        $pid = (int) $this->params()->fromRoute('pid', 0);
        $sid = (int) $this->params()->fromRoute('sid', 0);
        
        if (empty($cid)) {
            return $this->redirect()->toRoute('clients');
        } 
        
        if (empty($pid)) {
            return $this->redirect()->toRoute('clients');
        } 
        
        if (empty($sid)) {
            return $this->redirect()->toRoute('clients');
        } 
        
        $space = $this->getEntityManager()->find('Space\Entity\Space', $sid);
        if (empty($space)) {
            return $this->redirect()->toRoute('clients');
        } 
        
        if ($pid != $space->getProject()->getProjectId()) {
            return $this->redirect()->toRoute('clients');
        } 
        
        if ($cid != $space->getProject()->getClient()->getClientId()) {
            return $this->redirect()->toRoute('clients');
        } 
        
        // check privileges
        $project = $space->getProject();
        if ($project->getClient()->getUser()->getUserId()!=$this->identity()->getUserId()) {
            if (!$this->isGranted('admin.all')) {
                if (!$this->isGranted('project.share') || ($this->isGranted('project.share') && ($project->getClient()->getUser()->getCompany()->getCompanyId() != $this->identity()->getCompany()->getCompanyId()))) {
                    $passed = false;
                    foreach ($project->getCollaborators() as $user) {
                        if ($user->getUserId()==$this->identity()->getUserId()) {
                            $passed = true;
                            break;
                        }
                    }
                    if (!$passed) {
                        foreach ($project->getClient()->getCollaborators() as $user) {
                            if ($user->getUserId()==$this->identity()->getUserId()) {
                                $passed = true;
                                break;
                            }
                        }
                        if (!$passed) {
                            return $this->redirect()->toRoute('clients');
                        }
                    }
                    
                } 
            }
        }
        
        $this->setSpace($space);
        $this->setProject($space->getProject());

        $this->amendNavigation();
        
        return parent::onDispatch($e);
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
     * @return \Project\Controller\ProjectitemController
     */
    public function setProject(\Project\Entity\Project $project) {
        $this->project = $project;
        $this->getView()->setVariable('project', $project);
        return $this;
    }
    
    
    /**
     * get project
     * @return \Space\Entity\Space
     */
    public function getSpace() {
        return $this->space;
    }

    /**
     * set space
     * @param \Space\Entity\Project $space
     * @return \Space\Controller\SpaceitemController
     */
    public function setSpace(\Space\Entity\Space $space) {
        $this->space = $space;
        $this->getView()->setVariable('space', $space);
        return $this;
    }
    
    public function amendNavigation() {
        // check current location
        $action = $this->params('action');
        
        // get client
        $project = $this->getProject();
        $client = $project->getClient();
        
        // grab navigation object
        $navigation = $this->getServiceLocator()->get('navigation');
        
        $navigation->addPage(array(
            'type' => 'uri',
            'ico'=> 'icon-user',
            'order'=>0,
            'uri'=> '/client-'.$client->getClientId().'/',
            'label' => 'Client #'.str_pad($client->getClientId(), 5, "0", STR_PAD_LEFT),
        ));/**/
        
        $navigation->addPage(array(
            'type' => 'uri',
            'active'=>true,  
            'ico'=> 'icon-user',
            'order'=>1,
            'uri'=> '/client/',
            'label' => 'Clients',
            'skip' => true,
            'pages' => array(
                array (
                    'type' => 'uri',
                    'active'=>true,  
                    'ico'=> 'icon-user',
                    'skip' => true,
                    'uri'=> '/client-'.$client->getClientId().'/',
                    'label' => $client->getName(),
                    'mlabel' => 'Client #'.str_pad($client->getClientId(), 5, "0", STR_PAD_LEFT),
                    'pages' => array(
                        array(
                            'type' => 'uri',
                            'active'=>true,  
                            'ico'=> 'icon-tag',
                            'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/',
                            'label' => $project->getName(),
                            'mlabel' => 'Project: '.str_pad($client->getClientId(), 5, "0", STR_PAD_LEFT).'-'.str_pad($project->getProjectId(), 5, "0", STR_PAD_LEFT),
                            'pages' => array(
                                array(
                                    'label' => 'Dashboard',
                                    'active'=>false,  
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/',
                                    'title' => ucwords($project->getName()).' Overview',
                                ),
                                array(
                                    'active'=>false,  
                                    'label' => 'Configuration',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/setup/',
                                    'title' => ucwords($project->getName()).' Setup',
                                ),
                                array(
                                    'active'=>($standardMode && ($action=='bluesheet')),  
                                    'label' => 'Blue Sheet',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/bluesheet/',
                                    'title' => ucwords($project->getName()).' Blue Sheet',
                                ),
                                array(
                                    'active'=>true,  
                                    'label' => 'System Setup',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/system/',
                                    'title' => ucwords($project->getName()).' System Setup',
                                    'pages' => array (
                                        array(
                                            'active'=>true,  
                                            'label' => ucwords($this->getSpace()->getName()),
                                            'uri' => '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/space-'.$this->getSpace()->getSpaceId().'/',
                                            'title' => 'Manage Space',
                                        )
                                    )
                                ),
                                array(
                                    'active'=>false,  
                                    'label' => 'System Model',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/model/',
                                    'title' => ucwords($project->getName()).' System Model',
                                ),
                                array(
                                    'active'=>false,  
                                    'permissions'=>array('project.write'),
                                    'label' => 'Document Wizard',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/document/index/',
                                    'title' => ucwords($project->getName()).' Document Wizard',
                                ),
                                array(
                                    'active'=>false,  
                                    'label' => 'Document Manager',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/document/viewer/',
                                    'title' => ucwords($project->getName()).' Document Manager',
                                ),
                                array(
                                    'active'=>false,  
                                    'label' => 'Project Explorer',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/document/explorer/',
                                    'title' => ucwords($project->getName()).' Project Explorer',
                                ),
                                array(
                                    'active'=>false,  
                                    'label' => 'Email Threads',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/email/',
                                    'title' => ucwords($project->getName()).' Email Threads',
                                ),
                                array(
                                    'active'=>false,  
                                    'permissions'=>array('project.collaborate'),
                                    'label' => 'Collaborators',
                                    'uri'=> '/client-'.$client->getClientId().'/project-'.$project->getProjectId().'/collaborators/',
                                    'title' => ucwords($project->getName()).' Collaborators',
                                ),
                            )
                        )
                    )
                )
            )
        ));
        
        

        
    }
    
}