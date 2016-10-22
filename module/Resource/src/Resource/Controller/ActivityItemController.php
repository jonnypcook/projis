<?php
namespace Resource\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Controller\AuthController;
use Zend\Mvc\MvcEvent;
use ZendGData\App\Exception;

class ActivityItemController extends ActivitySpecificController
{
    public function indexAction()
    {
        if ( !($this->getRequest()->isXmlHttpRequest()) )
        {
            //throw new \Exception('Access denied');
        }

        $activity = $this->getActivity();

        if ( empty($activity) )
        {
            return new JsonModel( Array( 'err' => true, 'msg' => 'No such activity found' ) );
        }
        $status = '';
        if ( empty($activity->getStatus()) )
        {
            $status = 0;
        }
        else
        {
            $status = $activity->getStatus()->getResourceStatusId();
        }


        $data = Array(
            'date'       => $activity->getDate()->format( 'd/m/Y' ),
            'resource'   => $activity->getResource()->getResourceId(),
            'costCode'   => $activity->getCostCode()->getCostCodeId(),
            'project'    => $activity->getProject(),
            'reference'  => $activity->getReference(),
            'details'    => $activity->getDetails(),
            'quantity'   => $activity->getQuantity(),
            'rate'       => $activity->getRate(),
            'status'     => $status,
            'start_date' => !empty($activity->getStartDate()) ? $activity->getStartDate()->format( 'd/m/Y' ) : '',
            'end_date'   => !empty($activity->getEndDate()) ? $activity->getEndDate()->format( 'd/m/Y' ) : ''
        );

        return new JsonModel( Array( 'err' => false, 'resource' => $data ) );
    }

    public function editAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'Access denied' );
            }

            $activity = $this->getActivity();
            $post     = $this->getRequest()->getPost();
            $data     = array( 'err' => false, 'info' => '' );

            $expectedDate = $this->params()->fromPost( 'edit_expected_date', null );
            $dateArr      = explode( '/', $expectedDate );

            // Validating start date
            $startDate = $this->params()->fromPost( 'start_date', null );
            if ( empty(trim( $startDate )) )
            {
                return new JsonModel( array( 'err' => true, 'info' => 'Start date cannot be empty' ) );
            }

            // Validating end date
            $endDate = $this->params()->fromPost( 'end_date', null );
            if ( empty(trim( $endDate )) )
            {
                return new JsonModel( array( 'err' => true, 'info' => 'End date cannot be empty' ) );
            }

            $arr     = explode( '/', $startDate );
            $startDt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $arr[1], $arr[0], $arr[2] ) ) );

            $arr   = explode( '/', $endDate );
            $endDt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $arr[1], $arr[0], $arr[2] ) ) );

            if ( !empty($expectedDate) )
            {
                $dt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2] ) ) );
                $activity->setDate( $dt );

                $activity->setStartDate( $startDt );
                $activity->setEndDate( $endDt );

                //$form->setData( $post );

                $resource = $this->getEntityManager()->find( 'Resource\Entity\Resource', $post['edit_resource'] );
                $costCode = $this->getEntityManager()->find( 'Resource\Entity\CostCode', $post['edit_costCode'] );

                $status = $this->getEntityManager()->find( 'Resource\Entity\Status', $post['status'] );
                $activity->setStatus( $status );

                $activity->setCostCode( $costCode );
                $activity->setResource( $resource );
                $activity->setDetails( $post['edit_details'] );
                $activity->setProject( $post['edit_project'] );
                $activity->setQuantity( $post['edit_quantity'] );
                $activity->setRate( $post['edit_rate'] );
                $activity->setReference( $post['edit_reference'] );

                $this->getEntityManager()->flush();
                $this->flashMessenger()->addSuccessMessage( 'Resource activity has been updated successfully!' );

                return new JsonModel( Array( 'err' => false, 'info' => 'Activity updated successfully.' ) );

                // Redirect to list of albums
                //

                //return $this->redirect()->toRoute( 'resource_activities' );
            }
            else
            {
                $data = array( 'err' => true, 'info' => 'Invalid date' );

            }
        }
        catch ( \Exception $ex )
        {
            $data = Array( 'err' => false, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        /*
        $this->setCaption( 'Resource Activity - Edit' );

        $em   = $this->getEntityManager();
        $form = new \Resource\Form\ResourceActivityForm( $em );

        $activity = $this->getActivity();

        $url = '/resource-activity/resource-' . $activity->getResourceActivityId() . '/edit';

        $form->setAttribute( 'action', $url )
            ->setAttribute( 'class', 'form-horizontal' );

        $form->bind( $activity );

        $request = $this->getRequest();

        $data = array( 'err' => false, 'info' => '' );
        if ( $request->isPost() )
        {
            $post = $request->getPost();
            //$form = new \Resource\Form\ResourceActivityForm( $this->getEntityManager() );
            //$ra   = new \Resource\Entity\ResourceActivity();
            $form->setInputFilter( $activity->getInputFilter() );
            $post = $request->getPost();

            $expectedDate = $this->params()->fromPost( 'expected_date', null );
            $dateArr      = explode( '/', $expectedDate );
            $data         = array( 'err' => false, 'info' => '' );
            if ( !empty($expectedDate) )
            {
                $dt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2] ) ) );
                $activity->setDate( $dt );

                //$form->setData( $post );

                if ( $form->isValid() )
                {

                    $resource = $this->getEntityManager()->find( 'Resource\Entity\Resource', $post['resource'] );
                    $costCode = $this->getEntityManager()->find( 'Resource\Entity\CostCode', $post['costCode'] );

                    $activity->setCostCode( $costCode );
                    $activity->setResource( $resource );


                    $this->getEntityManager()->flush();

                    // Redirect to list of albums
                    $this->flashMessenger()->addSuccessMessage( 'Resource activity has been updated successfully!' );

                    return $this->redirect()->toRoute( 'resource_activities' );

                }
                else
                {
                    $data = array( 'err' => true, 'info' => $form->getMessages() );
                }
            }
            else
            {
                $data = array( 'err' => true, 'info' => 'Invalid date' );

            }
        }

        $this->getView()
            ->setVariable( 'form', $form )
            ->setVariable( 'data', $data );

        return $this->getView();
        */
    }

    public function editProductAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'Access denied' );
            }

            $activity = $this->getActivity();
            $post     = $this->getRequest()->getPost();
            $data     = array( 'err' => false, 'info' => '' );

            // Validating product date
            $productEditDate = $this->params()->fromPost( 'productEditDate', null );
            if ( empty(trim( $productEditDate )) )
            {
                return new JsonModel( array( 'err' => true, 'info' => 'Product date cannot be empty' ) );
            }

            // Validating start date
            $productEditStartDate = $this->params()->fromPost( 'productEditStartDate', null );
            if ( empty(trim( $productEditStartDate )) )
            {
                return new JsonModel( array( 'err' => true, 'info' => 'Product start date cannot be empty' ) );
            }

            // Validating end date
            $productEditEndDate = $this->params()->fromPost( 'productEditEndDate', null );
            if ( empty(trim( $productEditEndDate )) )
            {
                return new JsonModel( array( 'err' => true, 'info' => 'Product end date cannot be empty' ) );
            }

            $arr = explode( '/', $productEditDate );
            $dt  = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $arr[1], $arr[0], $arr[2] ) ) );

            $arr     = explode( '/', $productEditStartDate );
            $startDt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $arr[1], $arr[0], $arr[2] ) ) );

            $arr   = explode( '/', $productEditEndDate );
            $endDt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $arr[1], $arr[0], $arr[2] ) ) );

            $activity->setDate( $dt );

            $activity->setStartDate( $startDt );
            $activity->setEndDate( $endDt );

            $resource = $this->getEntityManager()->find( 'Resource\Entity\Resource', 14 );
            $costCode = $this->getEntityManager()->find( 'Resource\Entity\CostCode', 6 );

            $status = $this->getEntityManager()->find( 'Resource\Entity\Status', $post['productEditStatus'] );
            $activity->setStatus( $status );

            $activity->setCostCode( $costCode );
            $activity->setResource( $resource );
            if ( empty($activity->getDetails()) )
            {
                $activity->setDetails('');
            }
            $activity->setProject( $post['productEditProject'] );
            $activity->setQuantity( $post['productEditQuantity'] );
            $activity->setRate( $post['productEditRate'] );
            $activity->setReference( $post['productEditReference'] );

            $this->getEntityManager()->flush();
            $this->flashMessenger()->addSuccessMessage( 'The product activity has been updated successfully!' );

            return new JsonModel( Array( 'err' => false, 'info' => 'The product activity has been updated successfully.' ) );
        }
        catch ( \Exception $ex )
        {
            //$data = Array( 'err' => false, 'info' => array( 'ex' => $ex->getMessage() ) );
            return new JsonModel( Array( 'err' => false, 'info' => array( 'ex' => $ex->getMessage() ) ) );
        }
    }

    public function editInvoiceAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'Access denied' );
            }

            $activity = $this->getActivity();
            $post     = $this->getRequest()->getPost();
            $data     = array( 'err' => false, 'info' => '' );

            // Validating invoice date
            $date = $this->params()->fromPost( 'invoiceEditDate', null );
            if ( empty(trim( $date )) )
            {
                return new JsonModel( array( 'err' => true, 'info' => 'Invoice date cannot be empty' ) );
            }

            $arr = explode( '/', $date );
            $dt  = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $arr[1], $arr[0], $arr[2] ) ) );

            $activity->setDate( $dt );

            $resource = $this->getEntityManager()->find( 'Resource\Entity\Resource', 15 ); // Set resource to Invoice
            $costCode = $this->getEntityManager()->find( 'Resource\Entity\CostCode', 7 ); // Set Cost COde to Invoice

            //$status = $this->getEntityManager()->find( 'Resource\Entity\Status', $post['productEditStatus'] );
            //$activity->setStatus( $status );
            $activity->setResource( $resource );
            $activity->setCostCode( $costCode );
            $activity->setDetails( $post['invoiceEditDetails'] );
            $activity->setProject( $post['invoiceEditProject'] );
            $activity->setQuantity( 1 );
            $activity->setRate( $post['invoiceEditRate'] );
            $activity->setReference( $post['invoiceEditReference'] );

            $this->getEntityManager()->flush();
            $this->flashMessenger()->addSuccessMessage( 'The invoice activity has been updated successfully!' );

            return new JsonModel( Array( 'err' => false, 'info' => 'The invoice activity has been updated successfully.' ) );
        }
        catch ( \Exception $ex )
        {
            return new JsonModel( Array( 'err' => false, 'info' => array( 'ex' => $ex->getMessage() ) ) );
        }
    }

    public function fetchActivityAction()
    {
        if ( !$this->request->isXmlHttpRequest() )
        {
            throw new \Exception( 'Access denied' );
        }

        $activity = $this->getActivity();
        //$post = $this->getRequest()->getPost();
    }

    /**
     * Remove resource activity from database
     *
     * @return \Zend\Http\Response
     */
    public function removeAction()
    {
        if ( !($this->request->isXmlHttpRequest()) )
        {
            throw new \Exception('Invalid url');
        }
        $resource = $this->getActivity();
        $this->getEntityManager()->remove( $resource );
        $this->getEntityManager()->flush();

        $this->flashMessenger()->addSuccessMessage( 'The resource activity has been removed successfully!' );


        return new JsonModel(array('err' => false, 'info' => 'The resource activity Removed Successfully'));
    }
}