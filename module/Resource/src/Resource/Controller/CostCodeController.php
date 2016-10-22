<?php
namespace Resource\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Controller\AuthController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use ZendService\ReCaptcha\Response;

class CostCodeController extends AuthController
{
    public function indexAction()
    {
        $this->setCaption( 'Cost Code - Add' );
        $em = $this->getEntityManager();

        // Fetch Resources to display on listing page
        $costCodes = $em->getRepository( 'Resource\Entity\CostCode' )->findCostCodes();

        // create form for add window
        $form = new \Resource\Form\CostCodeForm( $em );
        $url  = '/cost-codes/add';

        $form->setAttribute( 'action', $url )
            ->setAttribute( 'class', 'form-horizontal' );

        $formEdit = new \Resource\Form\CostCodeForm( $em );
        $url      = '/cost-codes/edit';

        $formEdit->setAttribute( 'action', $url )
            ->setAttribute( 'class', 'form-horizontal' );

        $formEdit->setName( 'CostCodeEditForm' );

        $this->getView()
            ->setVariable( 'costcodes', $costCodes )
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
                $form     = new \Resource\Form\CostCodeForm( $em );
                $costcode = new \Resource\Entity\CostCode();
                $form->setInputFilter( new \Resource\Filter\CostCodeFilter() );
                $post = $request->getPost();
                $form->setData( $post );

                $data = array( 'err' => false, 'info' => '' );

                if ( $form->isValid() )
                {
                    $costcode->exchangeArray( $form->getData() );
                    $this->getEntityManager()->persist( $costcode );
                    $this->getEntityManager()->flush();

                    // Redirect to list of albums
                    $this->flashMessenger()->addSuccessMessage( 'The cost code has been added successfully!' );

                    return new JsonModel( array( 'err' => false, 'info' => 'The cost code has been added successfully!' ) );
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
}