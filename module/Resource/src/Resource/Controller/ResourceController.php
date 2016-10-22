<?php
namespace Resource\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Controller\AuthController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use ZendService\ReCaptcha\Response;

class ResourceController extends AuthController
{
    public function indexAction()
    {
        $this->setCaption( 'Resource Add' );
        $em = $this->getEntityManager();

        // Fetch Resources to display on listing page
        $resources = $em->getRepository( 'Resource\Entity\Resource' )->findResources();

        // create form for add window
        $form = new \Resource\Form\ResourceForm( $em );
        $url  = '/resource-items/add';

        $form->setAttribute( 'action', $url )
            ->setAttribute( 'class', 'form-horizontal' );

        $formEdit = new \Resource\Form\ResourceForm( $em );
        $url      = '/resource-items/edit';

        $formEdit->setAttribute( 'action', $url )
            ->setAttribute( 'class', 'form-horizontal' );
        $formEdit->setName( 'ResourceEditForm' );

        $this->getView()
            ->setVariable( 'resources', $resources )
            ->setVariable( 'form', $form )
            ->setVariable( 'formEdit', $formEdit );

        return $this->getView();
    }

    public function addAction()
    {
        try
        {
            $request = $this->getRequest();
            if ( $request->isPost() )
            {
                $em       = $this->getEntityManager();
                $form     = new \Resource\Form\ResourceForm( $em );
                $resource = new \Resource\Entity\Resource();
                $form->setInputFilter( new \Resource\Filter\ResourceFilter() );
                $post = $request->getPost();
                $form->setData( $post );

                $data = array( 'err' => false, 'info' => '' );

                if ( $form->isValid() )
                {
                    $costCode = $em->find( 'Resource\Entity\CostCode', $post['costCode'] );
                    $resource->setCostCode( $costCode );
                    $resource->exchangeArray( $form->getData() );
                    $this->getEntityManager()->persist( $resource );
                    $this->getEntityManager()->flush();

                    // Redirect to list of albums
                    $this->flashMessenger()->addSuccessMessage( 'Resource has been added successfully!' );

                    return new JsonModel( array( 'err' => false, 'info' => 'Resource has been added successfully!' ) );
                    //return $this->redirect()->toRoute( 'resource_activities' );

                }
                else
                {
                    $data = array( 'err' => true, 'info' => $form->getMessages() );
                }
            }
        }
        catch ( \Exception $ex )
        {
            return new JsonModel( array( 'err' => true, 'info' => $ex->getMessage() ) );
        }
    }

    public function editAction()
    {
        try
        {
            if ( !($this->request->isXmlHttpRequest()) )
            {
                throw new \Exception( 'Illegal Request' );
            }

            $post     = $this->getRequest()->getPost();
            $em       = $this->getEntityManager();
            $resource = $em->getRepository( '\Resource\Entity\Resource' )->find( $post['resource_id'] );

            if ( !($resource instanceof \Resource\Entity\Resource) )
            {
                throw new \Exception( 'No such resource found' );
            }

            $costCode = $em->find( 'Resource\Entity\CostCode', $post['costCode'] );
            $resource->setCostCode( $costCode );

            $resource->exchangeArray($post);
            $em->flush();

            return new JsonModel(array('err' => false, 'info' => 'Resouce updated succesfully!'));
        }
        catch ( \Exception $ex )
        {
            return new JsonModel( array( 'err' => true, 'info' => $ex->getMessage() ) );
        }
    }

}