<?php
namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Controller\AuthController;

use Zend\Mvc\MvcEvent;


abstract class ClientSpecificController extends AuthController
{
    /**
     *
     * @var Client\Entity\Client
     */
    private $client;
    
    
    public function onDispatch(MvcEvent $e) {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('clients');
        } 
        
        $client = $this->getEntityManager()->find('Client\Entity\Client', $id);
        
        if(empty($client)) {
            return $this->redirect()->toRoute('clients');
        }
        
        // now check privileges
        if ($client->getUser()->getUserId()!=$this->identity()->getUserId()) {
            if (!$this->isGranted('admin.all')) {
                if (!$this->isGranted('client.share') || ($this->isGranted('client.share') && ($client->getUser()->getCompany()->getCompanyId() != $this->identity()->getCompany()->getCompanyId()))) {
                    $passed = false;
                    foreach ($client->getCollaborators() as $user) {
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
        
        
        $this->setClient($client);
        $this->amendNavigation();
        
        return parent::onDispatch($e);
    }
    
    public function getClient() {
        return $this->client;
    }

    public function setClient(\Client\Entity\Client $client) {
        $this->client = $client;
        $this->getView()->setVariable('client', $client);
        
        return $this;
    }
    
    public function amendNavigation() {
        // check current location
        $action = $this->params('action');
        $buildingMode = ($this->params('controller')=='Client\Controller\Building');
        $buildingItemMode = ($this->params('controller')=='Client\Controller\BuildingItem');
        $contactMode = ($this->params('controller')=='Client\Controller\Contact');
        $standardMode = !$contactMode && !$buildingMode  && !$buildingItemMode;
        
        // get client
        $client = $this->getClient();
        
        $buildingArray = array (
            array(
                'active'=>($buildingMode && ($action=='add')),  
                'label' => 'Add',
                'uri' => '/client-'.$client->getClientId().'/building/add/',
                'title' => 'Add New Building',
            )
        );
        
        if ($buildingItemMode) {
            $bid = (int) $this->params()->fromRoute('bid', 0);
            $buildingArray[] = array(
                'active'=>($buildingItemMode && ($action=='index')),  
                'label' => 'Edit',
                'uri' => '/client-'.$client->getClientId().'/building-'.$bid.'/',
                'title' => 'Edit New Building',
            );
        }
        // grab navigation object
        $navigation = $this->getServiceLocator()->get('navigation');

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
                    'order'=>1,
                    'uri'=> '/client-'.$client->getClientId().'/',
                    'label' => $client->getName(),
                    'mlabel' => 'Client #'.str_pad($client->getClientId(), 5, "0", STR_PAD_LEFT),
                    'pages' => array(
                        array(
                            'label' => 'Dashboard',
                            'active'=>(($action=='index')&&($standardMode)),  
                            'uri' => '/client-'.$client->getClientId().'/',
                            'title' => ucwords($client->getName()).' Overview',
                            'pages' => array (
                                array(
                                    'active'=>(($action=='newproject')&&($standardMode)),  
                                    'label' => 'Create Project',
                                    'uri' => '/client-'.$client->getClientId().'/addproject/',
                                    'title' => 'Add New Project',
                                ),
                                array(
                                    'active'=>(($action=='newtrial')&&($standardMode)),  
                                    'label' => 'Create Trial',
                                    'uri' => '/client-'.$client->getClientId().'/addtrial/',
                                    'title' => 'Add New Trial',
                                ),
                                array(
                                    'label' => 'Activity Log',
                                    'active'=>($standardMode && ($action=='activity')),  
                                    'uri'=> '/client-'.$client->getClientId().'/activity/',
                                    'title' => 'Activity Log',
                                ),
                                array(
                                    'label' => 'Audit Log',
                                    'active'=>($standardMode && ($action=='audit')),  
                                    'uri'=> '/client-'.$client->getClientId().'/audit/',
                                    'title' => 'Audit Log',
                                ),
                            )
                        ),
                        array(
                            'active'=>($action=='setup'),  
                            'label' => 'Configuration',
                            'uri' => '/client-'.$client->getClientId().'/setup/',
                            'title' => ucwords($client->getName()).' Configuration',
                        ),
                        array(
                            'active'=>($buildingMode || $buildingItemMode),  
                            'label' => 'Buildings',
                            'uri' => '/client-'.$client->getClientId().'/building/',
                            'title' => ucwords($client->getName()).' Buildings',
                            'pages' => $buildingArray
                        ),
                        array(
                            'active'=>($contactMode),  
                            'permissions'=>array('contact.read'),
                            'label' => 'Contacts',
                            'uri' => '/client-'.$client->getClientId().'/contact/',
                            'title' => ucwords($client->getName()).' Contacts',
                            'pages' => array (
                                array(
                                    'active'=>($contactMode && ($action=='add')),  
                                    'label' => 'Add',
                                    'uri' => '/client-'.$client->getClientId().'/contact/add/',
                                    'title' => 'Add New Contact',
                                )
                            )
                        ),
                        array(
                            'active'=>($action=='collaborators'), 
                            'permissions'=>array('client.collaborate'),
                            'label' => 'Collaborators',
                            'uri' => '/client-'.$client->getClientId().'/collaborators/',
                            'title' => ucwords($client->getName()).' Collaborators',
                        ),
                        array(
                            'active'=>($action=='utilities'),  
                            'label' => 'Utilities',
                            'uri' => '/client-'.$client->getClientId().'/utilities/',
                            'title' => ucwords($client->getName()).' Utilities',
                        ),
                        array(
                            'active'=>($action=='filemanager'),  
                            'label' => 'File Manager',
                            'uri' => '/client-'.$client->getClientId().'/filemanager/',
                            'title' => ucwords($client->getName()).' File Manager',
                        )
                    )
                )
            )
        ));
        
     
    }

}