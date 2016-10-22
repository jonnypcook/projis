<?php
namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Entity\User; 
use Application\Controller\AuthController;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ClientController extends AuthController
{
    
    public function indexAction()
    {
        $this->setCaption('Clients');
		return new ViewModel(array(
		));
    }
    
    public function addAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());
        
        $form = new \Client\Form\ClientCreateForm($this->getEntityManager());
        $form->setAttribute('action', $this->getRequest()->getUri()); // set URI to current page
        $form->setAttribute('class', 'form-horizontal');
        
        $formAddr = new \Contact\Form\AddressForm($this->getEntityManager());
        
        if ($saveRequest) {
            try {
                $post = $this->getRequest()->getPost();
                $client = new \Client\Entity\Client();
                $form->bind($client);
                $form->setData($post);
                if ($form->isValid()) {
                    // now let's check the name
                    $clients = $this->getEntityManager()->getRepository('Client\Entity\Client')->findByName($client->getName());
                    if (!empty($clients)) {
                        $clientexist = array_shift($clients);
                        throw new \Exception('A client already exists with this name.  You cannot duplicate a client name and you must '
                                . 'either change the name entered on this form or use the existing client.<br />'
                                . '<a href="/client-'.$clientexist->getClientId().'/">Click here to go to existing client &raquo;</a>');
                    }

                    $client->setFinanceStatus(null);
                    $notes = empty($post['note'])?array():array_filter($post['note']);
                    $notes = json_encode($notes);
                    $client->setNotes($notes);
                    
                    $this->getEntityManager()->persist($client);
                    
                    $formAddr->setData($post);
                    $address = new \Contact\Entity\Address();
                    $formAddr->bind($address);
                    if ($formAddr->isValid()) {
                        $address->setClient($client);
                        $this->getEntityManager()->persist($address);
                    }
                    
                    $this->getEntityManager()->flush();
                    $this->flashMessenger()->addMessage(array('The client has been added successfully', 'Success!'));
                    
                    
                    $data = array('err'=>false, 'cid'=>$client->getClientId());
                    $this->AuditPlugin()->auditClient(100, $this->getUser()->getUserId(), $client->getClientId(), array());/**/
                    
                    // now synchronize the google docs location
                    $documentService = $this->getServiceLocator()->get('DocumentService');
                    $documentService->setClient($client);
                    $documentService->synchronize();

                } else {
                    $data = array('err'=>true, 'info'=>$form->getMessages());
                }
                
                
                    
            } catch (\Exception $ex) {
                $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
            }

            return new JsonModel(empty($data)?array('err'=>true):$data);/**/
        } else {
            $this->setCaption('Add New Client');

            $form->get('user')->setValue($this->getUser()->getUserId());
            
            $this->getView()
                    ->setVariable('form', $form)
                    ->setVariable('formAddr', $formAddr);
            return $this->getView();
        }            
            
    }
    
    public function listAction() {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }
        
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('Client\Entity\Client', 'c')
            ->innerJoin('c.user', 'u');
        
        $viewMode = $this->params()->fromQuery('fViewMode',1);

        
        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        $keyword = $this->params()->fromQuery('sSearch','');
        $keyword = trim($keyword);
        if (!empty($keyword)) {
            if (preg_match('/^[\d]+$/', trim($keyword))) {
                $queryBuilder->andWhere('c.clientId LIKE :cid')
                ->setParameter('cid', '%'.$keyword.'%');
            } else {
                $queryBuilder->andWhere('c.name LIKE :name')
                ->setParameter('name', '%'.trim(preg_replace('/[*]+/','%',$keyword),'%').'%');
            }
        } else { // if we keyword search then ignore filter setting
            $checkViewMode = true;
            if (!$this->isGranted('admin.all')) {
                if (!$this->isGranted('client.share')) {
                    $checkViewMode = false;
                    $queryBuilder->leftJoin("c.collaborators", "col", "WITH", "col=:userId");
                    $queryBuilder->andWhere('u.userId = :userId OR col.userId = :userId')
                            ->setParameter('userId', $this->getUser()->getUserId());
                } 
            }

            if ($checkViewMode) {
                switch ($viewMode) {
                    case 1:
                        $queryBuilder->leftJoin("c.collaborators", "col", "WITH", "col=:userId");
                        $queryBuilder->andWhere('u.userId = :userId OR col.userId = :userId')
                                ->setParameter('userId', $this->getUser()->getUserId());
                        break;
                    case 2:
                        $queryBuilder->andWhere('u.userId = :userId')
                                ->setParameter('userId', $this->getUser()->getUserId());
                        break;
                    case 3:
                        $queryBuilder->leftJoin("c.collaborators", "col", "WITH", "col=:userId");
                        $queryBuilder->andWhere('u.company = :companyId OR col.userId = :userId')
                                ->setParameter('companyId', $this->getUser()->getCompany()->getCompanyId())
                                ->setParameter('userId', $this->getUser()->getUserId());
                        break;
                }
            }            
        }      
        

        /*
         * Ordering
         */
        $aColumns = array('c.name','u.forename','c.projects','c.jobs','c.completed','c.created', 'c.clientId');
        $orderByP = $this->params()->fromQuery('iSortCol_0',false);
        $orderBy = array();
        if ($orderByP!==false)
        {
            for ( $i=0 ; $i<intval($this->params()->fromQuery('iSortingCols',0)) ; $i++ )
            {
                $j = $this->params()->fromQuery('iSortCol_'.$i);

                if ( $this->params()->fromQuery('bSortable_'.$j, false) == "true" )
                {
                    $dir = $this->params()->fromQuery('sSortDir_'.$i,'ASC');
                    if (is_array($aColumns[$j])) {
                        foreach ($aColumns[$j] as $ac) {
                            $orderBy[] = $ac." ".$dir;
                        }
                    } else {
                        $orderBy[] = $aColumns[$j]." ".($dir);
                    }
                }
            }

        }  
        if (empty($orderBy)) {
            $orderBy[] = 'c.name ASC';
        } 
        
        foreach ($orderBy as $ob) {
            $queryBuilder->add('orderBy', $ob);
        }
        
        /**/  
        
        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($queryBuilder))
        );

        $length = $this->params()->fromQuery('iDisplayLength', 10);
        $start = $this->params()->fromQuery('iDisplayStart', 1);
        $start = (floor($start / $length)+1);
        
        
        $paginator
            ->setCurrentPageNumber($start)
            ->setItemCountPerPage($length);
        
        $data = array(
            "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
            "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
            "iTotalRecords" => $paginator->getcurrentItemCount(),
            "aaData" => array()
        );/**/

        
        foreach ($paginator as $page) {
            //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
            $data['aaData'][] = array (
                '<a href="javascript:" class="action-client-edit"  pid="'.$page->getclientId().'">'.$page->getName().'</a>',
                $page->getUser()->getHandle(),
                'n/a',
                'n/a',
                $page->getCreated()->format('d/m/Y H:i'),
                str_pad($page->getClientId(), 5, "0", STR_PAD_LEFT),
                '<button class="btn btn-primary action-client-edit" pid="'.$page->getclientId().'" ><i class="icon-pencil"></i></button>',
            );
        }
        
        return new JsonModel($data);/**/
    }    
    

}