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

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ProductController extends AuthController
{
    public function catalogAction()
    {
        $this->setCaption('Product Catalog');
        $form = new \Product\Form\ProductConfigForm($this->getEntityManager());
        $form->setAttribute('action', '/product/add/')
            ->setAttribute('class', 'form-horizontal');
        
        $this->getView()
                ->setVariable('form', $form)
                ;
        
        return $this->getView();
    }
    
    public function listAction() {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }
        
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from('Product\Entity\Product', 'p')
            ->innerJoin('p.brand', 'b')
            ->innerJoin('p.type', 't')
            ->where('t.service = 0');
        
        
        
        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        $keyword = $this->params()->fromQuery('sSearch','');
        $keyword = trim($keyword);
        if (!empty($keyword)) {
            $queryBuilder->andWhere('p.model LIKE :model')
                ->setParameter('model', '%'.trim(preg_replace('/[*]+/','%',$keyword),'%').'%');
        }        
        

        /*
         * Ordering
         */
        $aColumns = array('p.model',/*'p.description',/**/'b.name','t.name','p.ppu','p.eca');
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
            $orderBy[] = 'p.model ASC';
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
            $url = $this->url()->fromRoute('productitem',array('pid'=>$page->getproductId()));
            $data['aaData'][] = array (
                '<a href="'.$url.'" pid="'.$page->getproductId().'">'.$page->getModel().'</a>',
                //$page->getDescription(),
                $page->getBrand()->getName(),
                $page->getType()->getName(),
                number_format($page->getPPU(),2),
                '<span class="btn btn-'.($page->getECA()?'success':'danger').'"><i class="icon-'.($page->getECA()?'ok':'remove').'"></i></span>',
                '<a class="btn btn-primary" href="'.$url.'" ><i class="icon-pencil"></i></a> '
                . '<a class="btn btn-success copy-product" href="javascript:" data-productId="'.$page->getproductId().'" data-model="'.$page->getModel().'" ><i class="icon-copy"></i></a>',
            );
        }
        
        return new JsonModel($data);/**/
    }
    
    public function copyproductAction() {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            $em = $this->getEntityManager();
            
            $post = $this->getRequest()->getPost();
            $productId = $post['productId'];
            $newProductModel = $post['newProductModel'];
            
            $errs = array();
            if (empty($productId)) {
                throw new \Exception('product identifier not found');
            }
            
            if (empty($newProductModel)) {
                throw new \Exception('new model name not found');
            }
            
            $product = $this->getEntityManager()->find('Product\Entity\Product', $productId);
            if (empty($product)) {
                throw new \Exception('product not found');
            }
            
            $dql = 'SELECT COUNT(p) FROM Product\Entity\Product p WHERE p.model = :model';
            $q = $this->getEntityManager()->createQuery($dql);
            $q->setParameters(array('model' => $newProductModel));
                
            $count = $q->getSingleScalarResult();
            
            if ($count>0) {
                return new JsonModel(array('err'=>true, 'info'=>array(
                    'newProductModel'=>array(
                        'product model already exists on system'
                    )
                )));
            }
            
            
            // now duplicate
            $productNew = new \Product\Entity\Product();
            $info = $product->getArrayCopy();
            unset($info['inputFilter']);
            unset($info['productId']);
            

            $productNew->populate($info);
            $productNew->setModel($newProductModel);
            
            $em->persist($productNew);              
            $em->flush();
            
            $this->flashMessenger()->addMessage(array(
                'The product has been successfully duplicated', 'Success!'
            ));
            
            $data = array('err'=>false, 'productId'=>$productNew->getProductId());
            
            $this->AuditPlugin()->audit(321, $this->getUser()->getUserId(), array('product'=>$productNew->getProductId()));
            
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    public function addAction() {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            
            $post = $this->getRequest()->getPost();

            $form = new \Product\Form\ProductConfigForm($this->getEntityManager());
            $product = new \Product\Entity\Product();
            $form->bind($product);
            $form->setBindOnValidate(true);
            
            $form->setData($post);

            if ($form->isValid()) {
                // find sagepay code
                $dql = 'SELECT MAX(p.sagepay) FROM Product\Entity\Product p WHERE p.brand = :brand';
                $q = $this->getEntityManager()->createQuery($dql);
                $q->setParameters(array('brand' => $form->get('brand')->getValue()));

                $sageCode = $q->getSingleScalarResult();
                $sageCode++;

                $product->setSagepay($sageCode);
                
                $form->bindValues();
                $product->setLeadtime($product->getBuild()->getLeadTime());
                $this->getEntityManager()->persist($product);
                $this->getEntityManager()->flush();

                $this->flashMessenger()->addMessage(array(
                    'The product &quot;'.$product->getModel().'&quot; has been added successfully', 'Success!'
                ));
                    
                $data = array('err'=>false, 'info'=>array(
                    'productId' => $product->getProductId()
                ));
                
                $this->AuditPlugin()->audit(321, $this->getUser()->getUserId(), array(
                    'product'=>$product->getProductId()
                ));
                
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }/**/
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    function findSpaceConfigAction () {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            $projectId = $this->params()->fromPost('projectId', false);
            $productId = $this->params()->fromPost('productId', false);
            
            if (empty($projectId)) {
                throw new \Exception('project parameter missing');
            }
            
            if (empty($productId)) {
                throw new \Exception('product parameter missing');
            }
            
            $em = $this->getEntityManager();
            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder
                ->select('sp.name, sp.root, s.quantity, s.systemId')
                ->from('Product\Entity\Product', 'p')
                ->innerJoin('p.systems', 's')
                ->innerJoin('s.space', 'sp')
                ->where('sp.project = :projectId')
                ->setParameter("projectId", $projectId)
                ->andWhere('p.productId = :productId')
                ->setParameter("productId", $productId)
                ->andWhere('p.type = 1')
                ;
        
            $query = $queryBuilder->getQuery();
            $systems = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            
            
            $data = array('err'=>false, 'data'=>$systems);
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    
    public function philipsAction()
    {
        $this->setCaption('Philips &copy; Product Catalog');
        $form = new \Product\Form\ProductPhilipsForm($this->getEntityManager());
        $form->setAttribute('action', '/product/addphilips/')
            ->setAttribute('class', 'form-horizontal');
        
        $form->get('eca')->setValue(true);
        $form->get('type')->setValue(1);
        
        $em = $this->getEntityManager();
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('b.name, b.philipsBrandId')
            ->from('Product\Entity\PhilipsBrand', 'b');
        
        $brands = $queryBuilder->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('c.name, c.philipsCategoryId')
            ->from('Product\Entity\PhilipsCategory', 'c');
        
        $categories = $queryBuilder->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        
        $this->getView()
                ->setVariable('brands', $brands)
                ->setVariable('categories', $categories)
                ->setVariable('form', $form)
                ;
        
        return $this->getView();
    }
    
    public function addPhilipsAction() {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            $post = $this->getRequest()->getPost();
            $form = new \Product\Form\ProductPhilipsForm($this->getEntityManager());
            $form->setInputFilter(new \Product\Filter\ProductPhilipsFilter());
            $form->setData($post);

            if ($form->isValid()) {
                if (empty($post['ppid'])) {
                    throw new \Exception ('No Philips product specified');
                }
                $philips = $this->getEntityManager()->find('\Product\Entity\Philips', $post['ppid']);
                
                if (!($philips instanceof \Product\Entity\Philips)) {
                    throw new \Exception ('No Philips product found');
                }
                
                if ($philips->getProduct()) {
                    throw new \Exception ('An 8point3 product is already created for this Philips product');
                }
                
                $product = new Product();
                
                $hydrator = new DoctrineHydrator($this->getEntityManager(),'Product\Entity\Product');
                $hydrator->hydrate(
                    array (
                    'brand'=>6,// Philips (general)
                    'model'=>80000+$philips->getPhilipsId(),
                    'cpu'=>$philips->getCpu(),
                    'ppu'=>$philips->getPpu(),
                    'ibppu'=>0,
                    'ppu_trial'=>0,
                    'active'=>true,
                    'mcd'=>true,
                    'sagepay'=>'',
                    'build'=>1,
                    'attributes'=>json_encode(array(
                        'philips'=>array(
                            'ppid'=>$philips->getPhilipsId(),
                            'eoc'=>$philips->getEOC(),
                            'model'=>trim($philips->getModel()),
                        )
                    )),
                )+$form->getData(),
                $product);

                $this->getEntityManager()->persist($product);
                $philips->setProduct($product);
                $this->getEntityManager()->persist($philips);
                $this->getEntityManager()->flush();
                
                $data = array('err'=>false, 'info'=>array(
                    'productId' => $product->getProductId()
                ));
                
                $this->AuditPlugin()->audit(321, $this->getUser()->getUserId(), array(
                    'product'=>$product->getProductId(), 'philips'=>$philips->getPhilipsId()
                ));
                
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }/**/
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    public function listPhilipsAction() {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }
        
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from('Product\Entity\Philips', 'p')
            ->innerJoin('p.brand', 'b')
            ->innerJoin('p.category', 'c')
            ;
        
        
        
        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        $fBrand = $this->params()->fromQuery('fBrand',false);
        if (!empty($fBrand)) {
            $queryBuilder->andWhere('b.philipsBrandId = :brand')->setParameter('brand', $fBrand);
        }/**/
        
        $fCategory = $this->params()->fromQuery('fCategory',false);
        if (!empty($fCategory)) {
            $queryBuilder->andWhere('c.philipsCategoryId = :category')->setParameter('category', $fCategory);
        }/**/
        
        $keyword = $this->params()->fromQuery('sSearch','');
        $keyword = trim($keyword);
        if (!empty($keyword)) {
            if (preg_match('/^[\d]{12}$/', $keyword)) {
                $queryBuilder->andWhere('p.nc = :nc')
                ->setParameter('nc', $keyword);
            } elseif (preg_match('/^[\d]{8}$/', $keyword)) {
                $queryBuilder->andWhere('p.eoc = :eoc')
                ->setParameter('eoc', $keyword);
            } else {
                $queryBuilder->andWhere('p.model LIKE :model')
                ->setParameter('model', '%'.trim(preg_replace('/[*]+/','%',$keyword),'%').'%');
            }
        }        
        

        /*
         * Ordering
         */
        $aColumns = array('p.model','p.nc','p.eoc','c.name','b.name','p.ppu','p.product');
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
            $orderBy[] = 'p.model ASC';
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
            $addable = !($page->getProduct() instanceof Product);
            $data['aaData'][] = array (
                '<a class="more-info" href="javascript:" '
                . 'data-model="'.$page->getModel().'" '
                . 'data-desc="'.$page->getDescription().'" '
                . 'data-brand="'.$page->getBrand()->getName().'" '
                . 'data-category="'.$page->getCategory()->getName().'" '
                . 'data-nc="'.$page->getNC().'" '
                . 'data-eoc="'.$page->getEOC().'" '
                . 'data-tmin="'.$page->gettargetMin().'" '
                . 'data-tmax="'.$page->gettargetMax().'" '
                . 'data-ntrade="'.$page->getnetTrade().'" '
                . 'data-cpu="'.$page->getCpu().'" '
                . 'data-ppu="'.$page->getPpu().'" '
                . 'data-8p3="'.($addable?'None':$page->getProduct()->getModel()).'" '
                . (!$addable?'data-pid="'.$page->getProduct()->getProductId().'" ':'')
                . '>'.$page->getModel().'</a>',
                $page->getNc(),
                $page->getEOC(),
                $page->getBrand()->getName(),
                $page->getCategory()->getName(),
                number_format($page->getPPU(),2),
                $addable?'':$page->getProduct()->getModel(),
                ($addable?
                '<button '
                . 'data-model="'.$page->getModel().'" '
                . 'data-desc="'.$page->getDescription().'" '
                . 'data-ppid="'.$page->getphilipsId().'" '
                . 'class="btn btn-success add-product"><i class="icon-plus"></i></button>':
                '<button '
                . 'data-pid="'.$page->getProduct()->getProductId().'" '
                . 'class="btn btn-warning view-product"><i class="icon-double-angle-right"></i></button>'),
            );
        }
        
        return new JsonModel($data);/**/
    }    
    
}