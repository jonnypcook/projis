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

use Contact\Form\ContactForm;


class ContactController extends ClientSpecificController
{
    
    public function onDispatch(MvcEvent $e) {
        return parent::onDispatch($e);
    }
    
    public function itemAction() {
        $cid = (int) $this->params()->fromRoute('cid', 0);
        if (!$cid) {
            return $this->redirect()->toRoute('client-'.$this->getClient()->getClientId());
        }
        
        $contact = $this->getEntityManager()->find('Contact\Entity\Contact', $cid);
        
        if(empty($contact)) {
            return $this->redirect()->toRoute('client-'.$this->getClient()->getClientId());
        }
        
        $post = $this->getRequest()->getPost();
        if (!empty($post['save'])) {
            try {
                // >> HACK!! to replace empty strings with NULL but avoid messing up relationships
                $rem = array();
                foreach ($post as $idx=>$value) {
                    if (empty($value)) {
                        if (!preg_match('/Id$/i', $idx)) {
                            $rem[] = $idx;
                        }
                    }
                }
                foreach ($rem as $idx) {
                    unset($post[$idx]);
                }
                // << HACK!! to replace empty strings with NULL but avoid messing up relationships

                $form = new ContactForm($this->getEntityManager(), $this->getClient()->getclientId());
                $form->bind($contact);
                $form->setData($post);
                
                if ($form->isValid()) {
                    $notes = empty($post['note'])?array():array_filter($post['note']);
                    $notes = json_encode($notes);
                    
                    $form->bindValues();
                    
                    $contact->setInfluence(empty($post['influenceId'])?null:$this->getEntityManager()->find('Contact\Entity\Influence', $post['influenceId']));
                    $contact->setMode(empty($post['modeId'])?null:$this->getEntityManager()->find('Contact\Entity\Mode', $post['modeId']));
                    $contact->setNotes($notes);
                    $contact->setAddress($this->getEntityManager()->find('Contact\Entity\Address', $form->get('addressId')->getValue()));
                    $contact->setTitle($this->getEntityManager()->find('Contact\Entity\Title', $form->get('titleId')->getValue()));
                    $contact->setBuyingType($this->getEntityManager()->find('Contact\Entity\BuyingType', $form->get('buyingtypeId')->getValue()));
                    
                    $this->getEntityManager()->flush();
                    $data = array('err'=>false);
                    
                    $this->AuditPlugin()->auditClient(105, $this->getUser()->getUserId(), $this->getClient()->getClientId(), array (
                        'data'=> array (
                            'name'=>trim(preg_replace('/[ ]+/',' ',$contact->getTitle()->getName().' '.$contact->getForename().' '.$contact->getSurname())),
                            'cid'=>$contact->getContactId(),
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
            return new JsonModel(array('err'=>false, 'info'=>array(
                'contactId'=> $contact->getContactId(),
                'forename' => $contact->getForename(),
                'surname' => $contact->getSurname(),
                'titleId' => ($contact->getTitle()?$contact->getTitle()->getTitleId():0),
                'buyingtypeId' => ($contact->getBuyingType()?$contact->getBuyingType()->getBuyingTypeId():0),
                'influence' => ($contact->getInfluence()?$contact->getInfluence()->getInfluenceId():0),
                'mode' => ($contact->getMode()?$contact->getMode()->getModeId():0),
                'position' => $contact->getPosition(),
                'telephone1' => $contact->getTelephone1(),
                'keywinresult'=> $contact->getKeywinresult(),
                'telephone2' => $contact->getTelephone2(),
                'email' => $contact->getEmail(),
                'addressId' => ($contact->getAddress()?$contact->getAddress()->getAddressId():0),
                'notes' => json_decode($contact->getNotes(), true),
            )));/**/
        }        
    }
    
    public function indexAction()
    {
        $this->setCaption('Contact Management');

        $contacts = $this->getEntityManager()->getRepository('Contact\Entity\Contact')->findByClientId($this->getClient()->getclientId());
        
        $form = new ContactForm($this->getEntityManager(), $this->getClient()->getclientId());
        $form->setAttribute('action', '/client-'.$this->getClient()->getClientId().'/contact-%c/'); // set URI to current page
        
        $formAddr = new \Contact\Form\AddressForm($this->getEntityManager());
        $formAddr->setAttribute('action', '/client-'.$this->getClient()->getClientId().'/addressadd/'); // set URI to current page
        $formAddr->setAttribute('class', 'form-horizontal');

        $this->getView()
                ->setVariable('contactId', $this->params()->fromQuery('contact', false))
                ->setVariable('contacts', $contacts)
                ->setVariable('form', $form)
                ->setVariable('formAddr', $formAddr);
        
        
        return $this->getView();
    }
    
    public function addAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());
        $this->setCaption('Add Contact');

        $form = new ContactForm($this->getEntityManager(), $this->getClient()->getclientId());
        $form->setAttribute('action', $this->getRequest()->getUri()); // set URI to current page
        
        $contact = new \Contact\Entity\Contact();
        $contact->setClient($this->getClient());
        
        $formAddr = new \Contact\Form\AddressForm($this->getEntityManager());
        $formAddr->setAttribute('action', '/client-'.$this->getClient()->getClientId().'/addressadd/'); // set URI to current page
        $formAddr->setAttribute('class', 'form-horizontal');

        // assign hydrator

        // bind object to form
        $form->bind($contact);
        
        
        if ($saveRequest) {
            try {
                if (!$this->getRequest()->isPost()) {
                    throw new \Exception('illegal method');
                }
                
                $post = $this->getRequest()->getPost();
                
                // >> HACK!! to replace empty strings with NULL but avoid messing up relationships
                $rem = array();
                foreach ($post as $idx=>$value) {
                    if (empty($value)) {
                        if (!preg_match('/Id$/i', $idx)) {
                            $rem[] = $idx;
                        }
                    }
                }
                foreach ($rem as $idx) {
                    unset($post[$idx]);
                }
                // << HACK!! to replace empty strings with NULL but avoid messing up relationships

                $form->setData($post);
                if ($form->isValid()) {
                    $notes = empty($post['notes'])?array():array_filter($post['notes']);
                    $notes = json_encode($notes);
                    $contact->setNotes($notes);
                    
                    $contact->setInfluence(empty($post['influenceId'])?null:$this->getEntityManager()->find('Contact\Entity\Influence', $post['influenceId']));
                    $contact->setMode(empty($post['modeId'])?null:$this->getEntityManager()->find('Contact\Entity\Mode', $post['modeId']));
                    
                    $contact->setAddress($this->getEntityManager()->find('Contact\Entity\Address', $form->get('addressId')->getValue()));
                    $contact->setTitle($this->getEntityManager()->find('Contact\Entity\Title', $form->get('titleId')->getValue()));
                    $contact->setBuyingType($this->getEntityManager()->find('Contact\Entity\BuyingType', $form->get('buyingtypeId')->getValue()));
                    $this->getEntityManager()->persist($contact);
                    $this->getEntityManager()->flush();
                    $this->flashMessenger()->addMessage(array('The contact has been added successfully', 'Success!'));
                    
                    $data = array('err'=>false, 'url'=>'/client-'.$this->getClient()->getClientId().'/contact/');
                    $this->AuditPlugin()->auditClient(103, $this->getUser()->getUserId(), $this->getClient()->getClientId(), array (
                        'data'=> array (
                            'name'=>trim(preg_replace('/[ ]+/',' ',$contact->getTitle()->getName().' '.$contact->getForename().' '.$contact->getSurname())),
                            'cid'=>$contact->getContactId(),
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