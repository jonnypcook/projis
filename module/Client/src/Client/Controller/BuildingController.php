<?php
namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Zend\Mvc\MvcEvent;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;

use Client\Form\BuildingCreateForm;


class BuildingController extends ClientSpecificController
{
    
    public function onDispatch(MvcEvent $e) {
        return parent::onDispatch($e);
    }
    
    public function indexAction()
    {
        $this->setCaption('Building Management');

        $buildings = $this->getEntityManager()->getRepository('Client\Entity\Building')->findByClientId($this->getClient()->getclientId());
        
        $this->getView()->setVariable('buildings', $buildings);
        
        return $this->getView();
    }
    
    public function addAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());
        $this->setCaption('Add Building');

        $building = new \Client\Entity\Building();
        $building->setClient($this->getClient());
        $form = new BuildingCreateForm($this->getEntityManager(), $this->getClient()->getclientId());
        $form->setAttribute('action', $this->getRequest()->getUri()); // set URI to current page
        
        $formAddr = new \Contact\Form\AddressForm($this->getEntityManager());
        $formAddr->setAttribute('action', '/client-'.$this->getClient()->getClientId().'/addressadd/'); // set URI to current page
        $formAddr->setAttribute('class', 'form-horizontal');

        // assign hydrator

        // bind object to form
        $form->bind($building);
        
        
        if ($saveRequest) {
            try {
                if (!$this->getRequest()->isPost()) {
                    throw new \Exception('illegal method');
                }
                
                $post = $this->getRequest()->getPost();
                $form->setData($post);
                if ($form->isValid()) {
                    $building->setAddress($this->getEntityManager()->find('Contact\Entity\Address', $form->get('addressId')->getValue()));
                    $this->getEntityManager()->persist($building);
                    $this->getEntityManager()->flush();
                    $this->flashMessenger()->addMessage(array('The building has been added successfully', 'Success!'));
                    
                    if (!empty($post['projectId'])) {
                        $data = array('err'=>false, 'url'=>'/client-'.$this->getClient()->getClientId().'/project-'.$post['projectId'].'/system/');
                    } else {
                        $data = array('err'=>false, 'url'=>'/client-'.$this->getClient()->getClientId().'/building/');
                    }
                    $this->AuditPlugin()->auditClient(106, $this->getUser()->getUserId(), $this->getClient()->getClientId(), array(
                        'data'=>  array(
                            'bid'=>$building->getBuildingId(),
                            'lbl'=>$building->getName()
                        )
                    ));
                } else {
                    $data = array('err'=>true, 'info'=>$form->getMessages());
                }
                
            } catch (\Exception $ex) {
                $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
            }

            return new JsonModel(empty($data)?array('err'=>true):$data);/**/
        } else {
            $this->getView()->setVariable('form', $form);
            $this->getView()->setVariable('formAddr', $formAddr);
            
        }
        
        return $this->getView();
    }
    
}