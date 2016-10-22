<?php
namespace Product\Controller;

// Authentication with Remember Me
// http://samsonasik.wordpress.com/2012/10/23/zend-framework-2-create-login-authentication-using-authenticationservice-with-rememberme/

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Entity\User; 
use Application\Controller\AuthController;

use Product\Entity\Product;
use Product\Entity\Type;
use Product\Entity\Brand;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;

class LegacyController extends AuthController
{
    public function catalogAction()
    {
        $form = new \Product\Form\LegacyConfigForm($this->getEntityManager());
        $form->setAttribute('action', '/legacy/add/')
            ->setAttribute('class', 'form-horizontal form-nomargin');
        
        $this->setCaption('Legacy Product Catalog');
        
        $query = $this->getEntityManager()->createQuery("SELECT p.model, p.ppu, p.eca, p.pwr, p.productId, p.attributes, b.name as brand, b.brandId, t.name as type, t.service, t.typeId "
                . "FROM Product\Entity\Product p "
                . "JOIN p.brand b "
                . "JOIN p.type t "
                . "WHERE p.active = 1 "
                . "ORDER BY b.name ASC, p.model ASC");
        $products = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        
		$this->getView()
                ->setVariable('products', $products)
                ->setVariable('form', $form)
                ;
        
        return $this->getView();
    }
    
    
    public function addAction() {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            
            $post = $this->getRequest()->getPost();
            if(empty($post['legacyId'])) {
                $legacy = new \Product\Entity\Legacy();
            } else {
                $legacy = $this->getEntityManager()->find('Product\Entity\Legacy', $post['legacyId']);
                if (!($legacy instanceof \Product\Entity\Legacy)) {
                    throw new \Exception('illegal legacy product');
                }
            }

            $form = new \Product\Form\LegacyConfigForm($this->getEntityManager());
            $form->bind($legacy);
            $form->setBindOnValidate(true);
            
            
            
            $form->setData($post);

            if ($form->isValid()) {
                $form->bindValues();
                $this->getEntityManager()->persist($legacy);
                $this->getEntityManager()->flush();

                $this->flashMessenger()->addMessage(array(
                    'The legacy product has been added successfully', 'Success!'
                ));
                    
                $data = array('err'=>false, 'info'=>array(
                    'legacyId' => $legacy->getLegacyId()
                ));
                
                $this->AuditPlugin()->audit(331, $this->getUser()->getUserId(), array(
                    'legacy'=>$legacy->getLegacyId()
                ));
                
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }/**/
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    
    public function listAction() {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }
        
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('l')
            ->from('Product\Entity\Legacy', 'l')
            ->innerJoin('l.category', 'c');
        
        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        $keyword = $this->params()->fromQuery('sSearch','');
        $keyword = trim($keyword);
        if (!empty($keyword)) {
            $queryBuilder->andWhere('l.description LIKE :description')
                ->setParameter('description', '%'.trim(preg_replace('/[*]+/','%',$keyword),'%').'%');
        }        
        

        /*
         * Ordering
         */
        $aColumns = array('l.description','c.name','l.pwr_item','l.created');
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
            $orderBy[] = 'l.category ASC';
            $orderBy[] = 'l.description ASC';
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
            $data['aaData'][] = array (
                '<a class="action-legacy-edit" href="javascript:" data-legacyid="'.$page->getlegacyId().'">'.$page->getDescription().'</a>',
                $page->getCategory()->getName(),
                $page->getTotalPwr().'W',
                $page->getCreated()->format('d/m/Y H:i'),
                '<button class="btn btn-primary action-legacy-edit" data-legacyid="'.$page->getlegacyId().'" ><i class="icon-pencil"></i></button>',
            );
        }
        return new JsonModel($data);/**/
    }
}