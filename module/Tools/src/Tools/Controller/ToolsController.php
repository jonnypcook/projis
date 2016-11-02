<?php
namespace Tools\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User,    Application\Entity\Address,    Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

use Application\Controller\AuthController;

class ToolsController extends AuthController
{
    
    public function indexAction()
    {
        $this->setCaption('Tools');

        //$this->getView()->setVariable('form', $form);
        return $this->getView();
    }
    
     public function rpCalculatorAction()
    {
        $this->setCaption('Remote Phosphor Calculator (Advanced)');

        //$this->getView()->setVariable('form', $form);
        return $this->getView();
    }

    /**
     * tool used to reset architectural configuration
     * @return JsonModel
     */
    public function resetArchitecturalConfigurationAction () {
        try {

            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }

            die('this tool has been disabled');

            $data = $this->getServiceLocator()->get('Model')->resetArchitectural();
            echo '<pre>', print_r($data, true), '</pre>';die();
            $data = array('err'=>false, 'info'=>$data);
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }

    /**
     * calculate the optimum architectural layout
     * @return JsonModel
     */
    public function rpQuickCalculateAction() {
        try {
            
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            // test values
            $productId = $this->params()->fromPost('productId', false);
            $length = $this->params()->fromPost('length', false);
            $maximumUnitLength = $this->params()->fromPost('maxunitlen', false);
            $maximumPhosphorLength = $this->params()->fromPost('maximumPhosphorLength', false);
            $mode = 1;

            if (empty($productId) || !preg_match('/^[\d]+$/', $productId)) {
                throw new \Exception('illegal product parameter');
            }
            
            if (empty($length) || !preg_match('/^[\d]+(.[\d]+)?$/', $length)) {
                throw new \Exception('illegal product parameter');
            }

            if (empty($maximumUnitLength) || !preg_match('/^[\d]+(.[\d]+)?$/', $maximumUnitLength)) {
                throw new \Exception('illegal maximum unit length parameter: ' . $maximumUnitLength);
            }

            if (!empty($maximumPhosphorLength) && !preg_match('/^[\d]+(.[\d]+)?$/', $maximumPhosphorLength)) {
                throw new \Exception('illegal maximum phosphor unit length parameter: ' . $maximumPhosphorLength);
            }

            // find product cost per unit
            $product = $this->getEntityManager()->find('Product\Entity\Product', $productId);
            if (!($product instanceof \Product\Entity\Product)) {
                throw new \Exception('illegal product selection');
            }

            // ensure product is architectural
            if ($product->getType()->getTypeId() != 3) { // architectural
                throw new \Exception('illegal product type');
            }

            // we now have a maximum
            if (empty($maximumPhosphorLength)) {
                if (!empty($product->getPhosphors())) {
                    foreach ($product->getPhosphors() as $phosphor) {
                        if ($phosphor->isDefault() === true) {
                            $maximumPhosphorLength = $phosphor->getLength();
                            break;
                        } elseif (empty($maximumPhosphorLength) || $phosphor->getLength() > $maximumPhosphorLength) {
                            $maximumPhosphorLength = $phosphor->getLength();
                        }
                    }
                }
            }

            if (empty($maximumPhosphorLength)) {
                throw new \Exception('maximum phosphor unit length not provided via parameter or product');
            }

            $data = $this->getServiceLocator()->get('Model')->findOptimumArchitectural($product, $length, $maximumUnitLength, $maximumPhosphorLength, $mode, array('alts'=>true));
            $data = array('err'=>false, 'info'=>$data);
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    public function barcodeAction() {
        \Zend\Barcode\Barcode::render(
            'code39',
            'image',
            array(
                'text' => 'ZEND-FRAMEWORK',
                'font' => 3
            ),
            array(
                'imageType'=>'png',
            )
        ); /**/
        
        
        
    }
    
         
}
