<?php
namespace Product\Controller;

// Authentication with Remember Me
// http://samsonasik.wordpress.com/2012/10/23/zend-framework-2-create-login-authentication-using-authenticationservice-with-rememberme/

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;
use Zend\View\Model\JsonModel;

use Application\Entity\User; 
use Application\Controller\AuthController;


class LegacyItemController extends AuthController
{
    
    public function indexAction()
    {
        die('You have reached this page by mistake');
    }

    public function retrieveAction() {
    	try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                //throw new \Exception('illegal request');
            }
            
            
            $legacyId = (int) $this->params()->fromRoute('lid', 0);
            $errs = array();
            if (empty($legacyId)) {
                throw new \Exception('note identifier not found');
            }
            
            $legacy = $this->getEntityManager()->getRepository('Product\Entity\Legacy')->findByLegacyId($legacyId, array('array'=>false));

            if (empty($legacy)) {
                throw new \Exception('legacy item not found');
            }
            
            $data = array('err'=>false, 'legacy'=>array(
                'legacyId'=>$legacy->getLegacyId(),
                'categoryId'=>$legacy->getCategory()->getCategoryId(),
                'description'=>$legacy->getDescription(),
                'quantity'=>$legacy->getQuantity(),
                'pwr_item'=>$legacy->getPwr_item(),
                'pwr_ballast'=>$legacy->getPwr_ballast(),
                'emergency'=>$legacy->getEmergency(),
                'dim_item'=>$legacy->getDim_item(),
                'dim_unit'=>$legacy->getDim_unit(),
                'productId'=>$legacy->getProduct()->getProductId(),
            ));
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }

   
}