<?php
namespace Product\Controller;

// Authentication with Remember Me
// http://samsonasik.wordpress.com/2012/10/23/zend-framework-2-create-login-authentication-using-authenticationservice-with-rememberme/

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Entity\User; 
use Application\Controller\AuthController;

use Zend\Mvc\MvcEvent;

use Zend\View\Model\JsonModel;

class ProductitemController extends AuthController
{
    /**
     * product item
     * @var \Product\Entity\Product 
     */
    protected $product;


    public function onDispatch(MvcEvent $e) {
        $pid = (int) $this->params()->fromRoute('pid', 0);
        $product = $this->getEntityManager()->find('\Product\Entity\Product', $pid);
        
        $this->setProduct($product);
        $this->getView()->setVariable('product', $product);
        $this->amendNavigation();
        
        return parent::onDispatch($e);
    }
    
    public function getProduct() {
        return $this->product;
    }

    public function setProduct(\Product\Entity\Product $product) {
        $this->product = $product;
        $this->getView()->setVariable('product', $product);
        return $this;
    }

    public function listPricingAction() {
        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }
        
        $em = $this->getEntityManager();
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from('Product\Entity\Pricing', 'p')
            ->where('p.product = '.$this->getProduct()->getProductId())
            ->orderBy('p.min', 'ASC');
        
        $query = $queryBuilder->getQuery();
        $pricing = $query->getResult();
        
        $data = array(
            "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
            "iTotalDisplayRecords" => count($this->getProduct()->getPricepoints()),
            "iTotalRecords" => count($this->getProduct()->getPricepoints()),
            "aaData" => array()
        );/**/

        foreach ($pricing as $pricePoint) {
            $data['aaData'][] = array (
                $pricePoint->getMin(),
                $pricePoint->getMax(),
                '&#163;'.number_format($pricePoint->getCpu(), 2),
                '&#163;'.number_format($pricePoint->getPpu(), 2),
                '<a class="btn btn-primary edit-pricepoint" href="javascript:" '
                . 'data-pricingId="'.$pricePoint->getPricingId().'" '
                . 'data-cpu="'.$pricePoint->getCPU().'" '
                . 'data-ppu="'.$pricePoint->getPPU().'" '
                . 'data-min="'.$pricePoint->getMin().'" '
                . 'data-max="'.$pricePoint->getMax().'" ><i class="icon-pencil"></i></a>&nbsp;'
                . '<a class="btn btn-danger delete-pricepoint" href="javascript:" '
                . 'data-pricingId="'.$pricePoint->getPricingId().'" ><i class="icon-remove"></i></a>',
            );
        }
        
        return new JsonModel($data);/**/
    }
    
    public function savePricingAction() {
        try {
            if (!$this->request->isXmlHttpRequest()) {
                throw new \Exception('illegal request type');
            }

            $pricingId = $this->params()->fromPost('pricingId', false);
            $update = false;
            if (!empty($pricingId)) {
                $pricing = $this->getEntityManager()->find('\Product\Entity\Pricing', $pricingId);
                if (!($pricing instanceof \Product\Entity\Pricing)) {
                    throw new \Exception('Could not find pricing');
                }
                $update = true;
            } else {
                $pricing = new \Product\Entity\Pricing();
            }
            $form = new \Product\Form\PricingForm($this->getEntityManager());
            $form->bind($pricing);

            $post = $this->params()->fromPost();
            $form->setData($post);
            
            if ($form->isValid()) {
                $min =(int) $form->get('min')->getValue();
                $max =(int) $form->get('max')->getValue();
                
                foreach ($this->getProduct()->getPricepoints() as $pricepoint) {
                    if (($min >= $pricepoint->getMin()) && ($min <= $pricepoint->getMax())) {
                        if ($pricepoint->getPricingId()!=$pricingId) {
                            throw new \Exception('quantity range overlaps with an existing range for this product');
                        }
                    }
                    if (($max >= $pricepoint->getMin()) && ($max <= $pricepoint->getMax())) {
                        if ($pricepoint->getPricingId()!=$pricingId) {
                            throw new \Exception('quantity range overlaps with an existing range for this product');
                        }
                    }
                }
                   
                if (!$update) {
                    $pricing->setProduct($this->getProduct());
                }
                $form->bindValues();

                $this->getEntityManager()->persist($pricing);
                $this->getEntityManager()->flush();
                $data = array('err'=>false);
                //$this->AuditPlugin()->auditProject(202, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId());
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }

        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        
        
        return new JsonModel($data);/**/
    }
    
    
    public function deletePricingAction() {
        try {
            if (!$this->request->isXmlHttpRequest()) {
                throw new \Exception('illegal request type');
            }

            $pricingId = $this->params()->fromPost('pricingId', false);

            if (empty($pricingId)) {
                throw new \Exception('Could not find pricing');
            } 
            
            $pricing = $this->getEntityManager()->find('\Product\Entity\Pricing', $pricingId);
            if (!($pricing instanceof \Product\Entity\Pricing)) {
                throw new \Exception('Could not find pricing');
            }

            if ($this->getProduct()->getProductId()!=$pricing->getProduct()->getProductId()) {
                throw new \Exception('Product pricing mismatch');
            }
            
            $this->getEntityManager()->remove($pricing);
            $this->getEntityManager()->flush();

            $data = array('err'=>false);

        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        
        
        return new JsonModel($data);/**/
    }
        
    public function indexAction()
    {
        $this->setCaption($this->getProduct()->getModel());
        $attributes = json_decode($this->getProduct()->getAttributes(), true);
        if (!empty($attributes['philips']['ppid'])) {
            $philips = $this->getEntityManager()->find('\Product\Entity\Philips', $attributes['philips']['ppid']);
            if ($philips instanceof \Product\Entity\Philips) {
                $this->getView()->setVariable('philips', $philips);
            }
        }
        
        
		return $this->getView();
    }

    public function setupAction() {
        $form = new \Product\Form\ProductConfigForm($this->getEntityManager(), array('itemMode'=>true));
        $form->setBindOnValidate(true);
        $form->bind($this->getProduct());
        
        
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            try{
                $post = $this->getRequest()->getPost();
                $form->setData($post);
                
                if ($form->isValid()) {
                    $form->bindValues();
                    $this->getEntityManager()->flush();
                    $data = array('err'=>false);
                    $this->AuditPlugin()->audit(323, $this->getUser()->getUserId(), array(
                        'product'=>$this->getProduct()->getProductId()
                    ));
                }else {
                    $data = array('err'=>true, 'info'=>$form->getMessages());
                }/**/
            } catch (\Exception $ex) {
                $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
            }
            return new JsonModel(empty($data)?array('err'=>true):$data);/**/
        } else {
            $form->setAttribute('action', '/product-'.$this->getProduct()->getProductId().'/setup/')
                ->setAttribute('class', 'form-horizontal');
            
            $formPricing = new \Product\Form\PricingForm($this->getEntityManager());
            $formPricing->setAttribute('action', '/product-'.$this->getProduct()->getProductId().'/savepricing/')
                ->setAttribute('class', 'form-horizontal');
            
            $this->getView()
                    ->setVariable('form', $form)
                    ->setVariable('formPricing', $formPricing);
            return $this->getView();
        }
    }
    
    
    public function amendNavigation() {
        // check current location
        $action = $this->params('action');
        
        
        // get client
        $product = $this->getProduct();
        
        // grab navigation object
        $navigation = $this->getServiceLocator()->get('navigation');

        $navigation->addPage(array(
            'type' => 'uri',
            'active'=>true,  
            'ico'=> 'icon-tags',
            'order'=>1,
            'uri'=> '/product/catalog/',
            'label' => 'Products',
            'skip' => true,
            'pages' => array(
                array (
                    'type' => 'uri',
                    'active'=>true,  
                    'ico'=> 'icon-tag',
                    'order'=>1,
                    'uri'=> '/product-'.$product->getProductId().'/',
                    'label' => $product->getModel(),
                    'mlabel' => 'Product #'.str_pad($product->getProductId(), 5, "0", STR_PAD_LEFT),
                    'pages' => array(
                        array(
                            'label' => 'Dashboard',
                            'active'=>($action=='index'),  
                            'uri' => '/product-'.$product->getProductId().'/',
                            'title' => ucwords($product->getModel()).' Overview',
                        ),
                        array(
                            'active'=>($action=='setup'),  
                            'label' => 'Configuration',
                            'uri' => '/product-'.$product->getProductId().'/setup/',
                            'title' => ucwords($product->getModel()).' Configuration',
                        ),
                        array(
                            'active'=>($action=='bom'),  
                            'label' => 'BOM Setup',
                            'uri' => '/product-'.$product->getProductId().'/bom/',
                            'title' => ucwords($product->getModel()).' BOM Configuration',
                        ),
                        array(
                            'active'=>($action=='documents'),  
                            'label' => 'Documents',
                            'uri' => '/product-'.$product->getProductId().'/documents/',
                            'title' => ucwords($product->getModel()).' Documents',
                        ),
                        array(
                            'active'=>($action=='images'),  
                            'label' => 'Image Gallery',
                            'uri' => '/product-'.$product->getProductId().'/images/',
                            'title' => ucwords($product->getModel()).' Image Gallery',
                        ),
                    )
                )
            )
        ));
        
     
    }

   
}