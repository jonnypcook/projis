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
            $minimumPhosphorLength = $this->params()->fromPost('minimumPhosphorLength', false);
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

            if (!empty($minimumPhosphorLength) && !preg_match('/^[\d]+(.[\d]+)?$/', $minimumPhosphorLength)) {
                throw new \Exception('illegal minimum phosphor unit length parameter: ' . $minimumPhosphorLength);
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
            if (empty($maximumPhosphorLength) && !empty($product->getColour())) {
                $queryBuilder = $this->getEntityManager()->createQueryBuilder();
                $queryBuilder
                    ->select('p')
                    ->from('Product\Entity\Phosphor', 'p')
                    ->where('p.colour=?1')
                    ->orderBy('p.default', 'DESC')
                    ->orderBy('p.length', 'DESC')
                    ->setParameter(1, $product->getColour());
                $query = $queryBuilder->getQuery();
                $phosphors = $query->getResult();

                if (!empty($phosphors)) {
                    foreach ($phosphors as $phosphor) {
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

            $data = $this->getServiceLocator()->get('Model')->findOptimumArchitectural($product, $length, $maximumUnitLength, $maximumPhosphorLength, $minimumPhosphorLength, $mode, array('alts'=>true));
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
