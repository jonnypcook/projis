<?php
namespace Resource\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Controller\AuthController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use ZendGData\App\Exception;

class ResourceItemController extends ResourceSpecificController
{
    public function indexAction()
    {
        $this->setCaption( 'Resource Item' );


        return $this->getView();
    }

    public function fetchResourceAction()
    {
        if ( !$this->request->isXmlHttpRequest() )
        {
            throw new \Exception( 'Access denied' );
        }

        $resource = $this->getResource();

        $return_value = Array( 'error' => 1 );
        if ( !empty($resource) )
        {
            $return_value = Array(
                'error'    => 0,
                'costCode' => $resource->getCostCode()->getCostCodeId(),
                'rate'     => $resource->getCost()
            );
        }

        return new JsonModel( $return_value );
    }

    public function removeAction()
    {
        try
        {
            if ( !($this->request->isXmlHttpRequest()) )
            {
                throw new \Exception( 'Illegal Request' );
            }

            $resource = $this->getResource();

            $this->getEntityManager()->remove( $resource );
            $this->getEntityManager()->flush();

            return new JsonModel( Array( 'err' => false, 'info' => 'Resource removed successfully!' ) );
        }
        catch ( \Exception $ex )
        {
            return new JsonModel( array( 'err' => true, 'info' => $ex->getMessage() ) );
        }

    }
}