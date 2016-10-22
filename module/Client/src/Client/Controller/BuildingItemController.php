<?php
namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Controller\AuthController;

use Zend\Mvc\MvcEvent;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;


class BuildingitemController extends ClientSpecificController
{
    private $building;
    
    public function getBuilding() {
        return $this->building;
    }

    public function setBuilding(\Client\Entity\Building $building) {
        $this->building = $building;
        $this->getView()->setVariable('building', $building);
        
        return $this;
    }
    
    public function onDispatch(MvcEvent $e) {
        $bid = (int) $this->params()->fromRoute('bid', 0);
        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$bid) {
            return $this->redirect()->toRoute('buildings', array('id'=>$id));
        } 
        
        $building = $this->getEntityManager()->find('Client\Entity\Building', $bid);
        
        if(empty($building)) {
            return $this->redirect()->toRoute('buildings', array('id'=>$id));
        }
        
        // now check privileges
        $this->setBuilding($building);

        
        
        return parent::onDispatch($e);
    }
    
    public function indexAction()
    {
        $this->setCaption('Building Management: '.$this->getBuilding()->getName());
        $form = new \Client\Form\BuildingCreateForm($this->getEntityManager(), $this->getClient()->getclientId());
        $form->setAttribute('action', '/client-'.$this->getClient()->getClientId().'/building-'.$this->getBuilding()->getBuildingId().'/save/'); // set URI to current page

        $form->bind($this->getBuilding());
        $form->get('addressId')->setValue($this->getBuilding()->getAddress()->getAddressId());
        $formAddr = new \Contact\Form\AddressForm($this->getEntityManager());
        $formAddr->setAttribute('action', '/client-'.$this->getClient()->getClientId().'/addressadd/'); // set URI to current page
        $formAddr->setAttribute('class', 'form-horizontal');
        
        $this->getView()
                ->setVariable('formAddr', $formAddr)
                ->setVariable('form', $form);
        
        return $this->getView();
    }
    
    public function saveAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal method');
            }
            
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('illegal method');
            }
            
            $form = new \Client\Form\BuildingCreateForm($this->getEntityManager(), $this->getClient()->getclientId());
            $form->bind($this->getBuilding());
            
            $post = $this->getRequest()->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                $this->getBuilding()->setAddress($this->getEntityManager()->find('Contact\Entity\Address', $form->get('addressId')->getValue()));
                $this->getEntityManager()->persist($this->getBuilding());
                $this->getEntityManager()->flush();
                
                $data = array('err'=>false);

                $this->AuditPlugin()->auditClient(108, $this->getUser()->getUserId(), $this->getClient()->getClientId(), array(
                    'data'=>  array(
                        'bid'=>$this->getBuilding()->getBuildingId(),
                    )
                ));/**/
                
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }

        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
}