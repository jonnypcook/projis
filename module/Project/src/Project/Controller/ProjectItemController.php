<?php
namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Entity\User;
use Application\Controller\AuthController;

use Project\Form\SetupForm;
use Space\Form\SpaceCreateForm;

use Zend\Mvc\MvcEvent;

use Zend\View\Model\JsonModel;

use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ProjectitemController extends ProjectSpecificController
{
    public function activityAction()
    {
        $this->setCaption( 'Activity' );
        $em  = $this->getEntityManager();
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();

        $query    = $em->createQuery( 'SELECT SUM(TIMESTAMPDIFF(MINUTE, a.startDt, a.endDt)) '
            . 'FROM Application\Entity\Activity a '
            . 'WHERE '
            . 'a.project=' . $this->getProject()->getProjectId()
        );
        $duration = $query->getSingleScalarResult();

        return $this->getView()->setVariable( 'duration', $duration );
    }

    public function auditAction()
    {
        $this->setCaption( 'Audit Log' );

        return $this->getView();
    }

    public function indexAction()
    {
        $this->setCaption( 'Project Dashboard' );
        $discount = ($this->getProject()->getMcd());

        $em     = $this->getEntityManager();
        $system = $this->getModelService()->billitems($this->getProject());

        $query     = $em->createQuery( 'SELECT count(d) '
            . 'FROM Project\Entity\DocumentList d '
            . 'WHERE '
            . 'd.project=' . $this->getProject()->getProjectId() . ' AND '
            . 'd.category IN (1, 2, 3)'
        );
        $proposals = $query->getSingleScalarResult();

        $audit = $em->getRepository( 'Application\Entity\Audit' )->findByProjectId( $this->getProject()->getProjectId(), true, array(
            'max'  => 8,
            'auto' => true,
        ) );

        $activities = $em->getRepository( 'Application\Entity\Activity' )->findByProjectId( $this->getProject()->getProjectId(), true, array(
            'max'  => 8,
            'auto' => true,
        ) );

        $formActivity = new \Application\Form\ActivityAddForm( $em, array(
            'projectId' => $this->getProject()->getProjectId(),
        ) );

        $formActivity
            ->setAttribute( 'action', '/activity/add/' )
            ->setAttribute( 'class', 'form-nomargin' );

        $contacts = $this->getProject()->getContacts();


        $ldMode = 0;
        foreach ( $this->getProject()->getStates() as $state )
        {
            if ( ($state->getCommand() & 1) == 1 )
            { // lighting design requested
                $ldMode = 1;
            }
            elseif ( ($state->getCommand() & 2) == 2 )
            { // lighting design completed
                $ldMode = 2;
                break;
            }
        }

        if ( empty($ldMode) )
        {
            $query  = $em->createQuery( "SELECT u.userId, u.forename, u.surname FROM Application\Entity\User u JOIN u.roles r WITH r.id = 7" );
            $ldTeam = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

            if ( empty($ldTeam) )
            {
                $ldMode = 600;
            }
            else
            {
                $formTask = new \Task\Form\AddTaskForm( $this->getEntityManager() );
                $formTask
                    ->setAttribute( 'action', '/task/add/' )
                    ->setAttribute( 'class', 'form-horizontal' );
                $this->getView()
                    ->setVariable( 'ldTeam', $ldTeam )
                    ->setVariable( 'formTask', $formTask );
            }
        }
        else
        { // let's find task
            $query = $em->createQuery( 'SELECT t '
                . 'FROM Task\Entity\Task t '
                . 'WHERE t.project=' . $this->getProject()->getProjectId() . ' '
                . 'AND t.taskType=2 '
                . 'ORDER BY t.completed DESC, t.created DESC' )->setMaxResults( 1 );

            $tasks = $query->getResult();
            if ( !empty($tasks) )
            {
                $task = array_shift( $tasks );
                $this->getView()
                    ->setVariable( 'task', $task );
            }
        }

        // add meeting items
        $formCalendarEvent = new \Application\Form\CalendarEventAdvancedAddForm( $this->getEntityManager(), array( 'companyId' => $this->getUser()->getCompany()->getCompanyId() ) );
        $formCalendarEvent
            ->setAttribute( 'action', '/calendar/addevent/' )
            ->setAttribute( 'class', 'form-horizontal' );

        $recipients     = array(
            'client' => array( 'label' => 'CLIENT CONTACTS', 'options' => array(), ),
            'projis' => array( 'label' => 'PROJIS CONTACTS', 'options' => array(), ),
        );
        $contactsClient = $em->getRepository( 'Contact\Entity\Contact' )->findByClientId( $this->getProject()->getClient()->getclientId() );
        foreach ( $contactsClient as $contact )
        {
            if ( empty($contact->getEmail()) )
            {
                continue;
            }
            $recipients['client']['options'][$contact->getEmail()] = $contact->getForename() . ' ' . $contact->getSurname();
        }


        $users = $em->getRepository( 'Application\Entity\User' )->findByCompany( $this->getUser()->getCompany()->getCompanyId() );
        foreach ( $users as $user )
        {
            if ( empty($user->getEmail()) )
            {
                continue;
            }
            if ( !empty($recipients['client']['options'][$user->getEmail()]) )
            { // cannot have duplicate email addresses
                continue;
            }
            $recipients['projis']['options'][$user->getEmail()] = $user->getName();
        }

        $formCalendarEvent->get( 'users' )->setAttribute( 'options', $recipients );
        $formCalendarEvent->get( 'users' )->setAttribute( 'style', 'width: 300px;' );

        $payback = $this->getModelService()->payback( $this->getProject() );


        $formPPU = new \Project\Form\PricePointUpdateForm();
        $formPPU->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/pricepointchange/' )
            ->setAttribute( 'class', 'form-horizontal' );

        $this->getView()
            ->setVariable( 'formCalendarEvent', $formCalendarEvent )
            ->setVariable( 'ldMode', $ldMode )
            ->setVariable( 'contacts', $contacts )
            ->setVariable( 'proposals', $proposals )
            ->setVariable( 'formActivity', $formActivity )
            ->setVariable( 'formPPU', $formPPU )
            ->setVariable( 'user', $this->getUser() )
            ->setVariable( 'audit', $audit )
            ->setVariable( 'figures', $payback['figures'] )
            ->setVariable( 'activities', $activities )
            ->setVariable( 'system', $system );

        return $this->getView();
    }

    public function pricepointchangeAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }


            $post = $this->params()->fromPost();
            $form = new \Project\Form\PricePointUpdateForm();
            $form
                ->setInputFilter( new \Project\Filter\PricePointUpdateFilter() )
                ->setData( $post );
            if ( $form->isValid() )
            {
                $cpu = false;
                if ( !empty($post['cpu']) && $this->isGranted( 'product.write' ) )
                {
                    /*
                    if ( $post['cpu'] >= ($post['ppu'] * 0.9) )
                    {
                        throw new \Exception( 'The cpu value is within 10% of the ppu value' );
                    }
                    */
                    if ( $post['cpu'] <= ($post['ppu'] * 0.1) )
                    {
                        throw new \Exception( 'The cpu value less than 10% of the ppu value' );
                    }

                    $cpu = round( $post['cpu'], 2 );
                }

                $em      = $this->getEntityManager();
                $systems = $this->getEntityManager()->getRepository( 'Space\Entity\System' )->findByProjectIdProductId( $this->getProject()->getProjectId(), $form->get( 'product' )->getValue() );
                $ppu     = $form->get( 'ppu' )->getValue();
                foreach ( $systems as $system )
                {
                    if ( (($system->getPPU() == $ppu) && ($cpu === false)) || (($system->getPPU() == $ppu) && ($system->getCPU() == $cpu)) )
                    {
                        continue;
                    }
                    $system->setPPU( $ppu );
                    if ( $cpu !== false )
                    {
                        $system->setCPU( $cpu );
                    }
                    $em->persist( $system );
                }

                $em->flush();
                $this->flashMessenger()->addMessage( array(
                    'The price of the selected product has been successfully updated across the system', 'Success!'
                ) );
                $data = array( 'err' => false );
                $this->AuditPlugin()->auditProject( 199, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), array(
                    'productId' => $form->get( 'product' )->getValue(),
                    'ppu'       => $ppu,
                ) );

            }
            else
            {
                $data = array( 'err' => true, 'info' => $form->getMessages() );
            }
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function modelAction()
    {
        $years = $this->params()->fromQuery( 'modelYears', false );
        if ( !empty($years) )
        {
            if ( preg_match( '/^[\d]{1,2}$/', $years ) )
            {
                if ( ($years > 0) && ($years <= 12) )
                {
                    if ( $this->getProject()->getModel() != $years )
                    {
                        $this->getProject()->setModel( $years );
                        $this->getEntityManager()->persist( $this->getProject() );
                        $this->getEntityManager()->flush();
                    }
                }
            }
        }
        $this->setCaption( 'Project Model' );
        $service = $this->getModelService()->payback( $this->getProject(), $this->getProject()->getModel() );

        //echo '<pre>', print_r($service, true), '</pre>';        die('STOP');
        $this->getView()
            ->setVariable( 'figures', $service['figures'] )
            ->setVariable( 'forecast', $service['forecast'] );

        return $this->getView();
    }

    public function forecastAction()
    {
        $this->setCaption( 'Project System Forecast' );
        $service = $this->getModelService()->payback( $this->getProject() );

        //$this->debug()->dump($service['forecast']);
        $this->getView()
            ->setVariable( 'figures', $service['figures'] )
            ->setVariable( 'forecast', $service['forecast'] );

        return $this->getView();
    }

    public function breakdownAction()
    {
        $this->setCaption( 'Project System Forecast' );
        $breakdown = $this->getModelService()->spaceBreakdown( $this->getProject() );

        $this->getView()
            ->setVariable( 'breakdown', $breakdown );

        return $this->getView();
    }


    public function surveyAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());

        $storedProps = array();
        foreach ( $this->getProject()->getProperties() as $propertyLink )
        {
            if ( ($propertyLink->getProperty()->getGrouping() & 32) == 32 )
            {
                $storedProps[$propertyLink->getProperty()->getName()] = $propertyLink;
            }
        }

        if ( $saveRequest )
        {
            try
            {
                if ( !$this->getRequest()->isPost() )
                {
                    throw new \Exception( 'illegal method' );
                }

                $post  = $this->params()->fromPost();
                $props = $this->getEntityManager()->getRepository( 'Application\Entity\Property' )->findByGrouping( array( 32 ) );


                $em = $this->getEntityManager();

                // save competitor information
                foreach ( $props as $prop )
                {
                    if ( !empty($post[$prop->getName()]) )
                    {
                        if ( isset($storedProps[$prop->getName()]) )
                        { // already exists
                            $obj = $storedProps[$prop->getName()];
                            if ( $obj->getValue() == $post[$prop->getName()] )
                            {
                                continue;
                            }
                        }
                        else
                        { // create new
                            $obj = new \Project\Entity\ProjectProperty();
                            $obj->setProject( $this->getProject() );
                            $obj->setProperty( $prop );
                        }

                        if ( is_array( $post[$prop->getName()] ) )
                        {
                            $arr = array();
                            foreach ( $post[$prop->getName()] as $value )
                            {
                                if ( !empty(trim( $value )) )
                                {
                                    $arr[] = $value;
                                }
                            }
                            if ( empty($arr) )
                            {
                                $em->remove( $obj );
                                continue;
                            }
                            else
                            {
                                $obj->setValue( json_encode( $arr ) );
                            }
                        }
                        else
                        {
                            $obj->setValue( $post[$prop->getName()] );
                        }

                        $em->persist( $obj );
                    }
                    else
                    {
                        if ( isset($storedProps[$prop->getName()]) )
                        {
                            $em->remove( $storedProps[$prop->getName()] );
                        }
                    }
                }
                $em->flush();
                $data = array( 'err' => false, );

            }
            catch ( \Exception $ex )
            {
                $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
            }

            return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
        }
        else
        {
            $this->setCaption( 'System Survey' );


            $questions = $this->getEntityManager()->getRepository( 'Application\Entity\Property' )->findByGrouping( 32 );
            $this->getView()
                ->setVariable( 'storedProps', $storedProps )
                ->setVariable( 'questions', $questions );

            return $this->getView();
        }
    }


    /**
     * system management action
     * @return Zend\View\Model\ViewModel
     */
    public function systemAction()
    {
        $this->setCaption( 'System Setup' );

        $spaces = $this->getEntityManager()->getRepository( 'Space\Entity\Space' )->findByProjectId( $this->getProject()->getProjectId(), array( 'root' => true ) );
        // if we don't have a root space (non physical default space) then we need to create
        if ( empty($spaces) )
        {
            $space = new \Space\Entity\Space();
            $space->setRoot( true );
            $space->setName( 'root' );
            $space->setProject( $this->getProject() );
            $this->getEntityManager()->persist( $space );
            $this->getEntityManager()->flush();
        }


        foreach ( $spaces as $spaceItem )
        {
            $space = $spaceItem;
            break;
        }

        // space create form
        $form = new SpaceCreateForm( $this->getEntityManager(), $this->getProject()->getClient()->getClientId() );
        $form->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/newspace/' ); // set URI to current page
        $form->setAttribute( 'class', 'form-horizontal' );

        // system create form
        $formSystem = new \Space\Form\SpaceAddProductForm( $this->getEntityManager() );
        $formSystem->setAttribute( 'class', 'form-horizontal' );
        $formSystem->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/space-' . $space->getSpaceId() . '/addsystem/' );
        $system = new \Space\Entity\System();
        $formSystem->bind( $system );

        $buildings = $this->getEntityManager()->getRepository( 'Client\Entity\Building' )->findByProjectId( $this->getProject()->getProjectId(), array( 'order' => 'building' ) );

        // get product information
        $query    = $this->getEntityManager()->createQuery( "SELECT p.model, p.ppu, p.eca, p.pwr, p.attributes, p.productId, b.name as brand, b.brandId, t.name as type, t.service, t.typeId "
            . "FROM Product\Entity\Product p "
            . "JOIN p.brand b "
            . "JOIN p.type t "
            . "WHERE p.active = 1 "
            . "ORDER BY b.name ASC, p.model ASC" );
        $products = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $query    = $this->getEntityManager()->createQuery( "SELECT l.legacyId, l.description, l.quantity, l.pwr_item, l.pwr_ballast, l.emergency, l.dim_item, l.dim_unit, c.maintenance, c.name as category, p.productId FROM Product\Entity\Legacy l JOIN l.category c LEFT JOIN l.product p ORDER BY l.category ASC, l.description ASC" );
        $legacies = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $systems = $this->getEntityManager()->getRepository( 'Space\Entity\System' )->findBySpaceId( $space->getSpaceId(), array( 'array' => true ) );

        // get backup information
        $query = $this->getEntityManager()->createQuery( "SELECT ps.saveId, ps.name, ps.created "
            . "FROM Project\Entity\Save ps "
            . "WHERE ps.project = {$this->getProject()->getProjectId()} "
            . "ORDER BY ps.activated DESC" );
        $saves = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );


        $this->getView()
            ->setVariable( 'saves', $saves )
            ->setVariable( 'space', $space )
            ->setVariable( 'form', $form )
            ->setVariable( 'formSystem', $formSystem )
            ->setVariable( 'products', $products )
            ->setVariable( 'legacies', $legacies )
            ->setVariable( 'buildings', $buildings )
            ->setVariable( 'systems', $systems );

        return $this->getView();
    }

    /**
     * list spaces in building
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function spaceListAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $post = $this->getRequest()->getPost();
            if ( empty($post['bid']) )
            {
                throw new \Exception( 'building identifier not found' );
            }

            if ( !preg_match( '/^[\d]+$/', $post['bid'] ) )
            {
                throw new \Exception( 'building identifier invalid' );
            }

            // note issue arises here
            $spaces = $this->getEntityManager()->getRepository( 'Space\Entity\Space' )->findByBuildingId( $post['bid'], $this->getProject()->getProjectId(), true, array( 'agg' => array( 'ppu' => true, 'cpu' => true, 'quantity' => true ) ) );

            $data = array( 'err' => false, 'spaces' => $spaces );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function setupAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());

        $this->setCaption( 'Project Configuration' );
        $form = new SetupForm( $this->getEntityManager(), $this->getProject()->getClient() );
        $form->setAttribute( 'action', $this->getRequest()->getUri() ); // set URI to current page

        $form->bind( $this->getProject() );
        $form->setBindOnValidate( true );

        if ( $saveRequest )
        {
            try
            {
                if ( !$this->getRequest()->isPost() )
                {
                    throw new \Exception( 'illegal method' );
                }

                $post = $this->params()->fromPost();
                $form->setData( $post );
                if ( $form->isValid() )
                {
                    if ( empty($form->get( 'contacts' )->getValue()) )
                    {
                        return new JsonModel( array(
                            'err' => true, 'info' => array(
                                'contacts' => array(
                                    'contacts' => 'You must select or create at least one contact'
                                )
                            )
                        ) );/**/
                    }
                    if ( empty($post['states']) )
                    {
                        $this->getProject()->setStates( new \Doctrine\Common\Collections\ArrayCollection() );
                    }
                    if ( empty($post['contacts']) )
                    {
                        $this->getProject()->setContacts( new \Doctrine\Common\Collections\ArrayCollection() );
                    }
                    $form->bindValues();

                    $this->getEntityManager()->flush();
                    $data = array( 'err' => false );
                    $this->AuditPlugin()->auditProject( 202, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId() );
                }
                else
                {
                    $data = array( 'err' => true, 'info' => $form->getMessages() );
                }
            }
            catch ( \Exception $ex )
            {
                $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
            }

            return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
        }
        else
        {
            //$form->get('name')->setAttribute('readonly', 'true');
            $this->getView()
                ->setVariable( 'form', $form );

            return $this->getView();
        }
    }

    public function buildingAddAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());
        $this->setCaption( 'Add Building' );

        $form = new \Client\Form\BuildingCreateForm( $this->getEntityManager(), $this->getProject()->getClient()->getclientId() );
        $form->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/building/add/' ); // set URI to current page

        $formAddr = new \Contact\Form\AddressForm( $this->getEntityManager() );
        $formAddr->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/addressadd/' ); // set URI to current page
        $formAddr->setAttribute( 'class', 'form-horizontal' );

        $this->getView()->setVariable( 'form', $form );
        $this->getView()->setVariable( 'formAddr', $formAddr );


        return $this->getView();
    }

    public function closeAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $this->getProject()->setCancelled( true );
            $this->getEntityManager()->persist( $this->getProject() );
            $this->getEntityManager()->flush();

            $data = array( 'err' => false );
            $this->AuditPlugin()->auditProject( 203, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId() );
            $this->flashMessenger()->addMessage( array(
                'The project has been marked as lost', 'Success!'
            ) );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    /**
     * Activate cancelled project and set cancelled status to false
     *
     * @return JsonModel
     */
    public function activateAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $this->getProject()->setCancelled( false );
            $this->getEntityManager()->persist( $this->getProject() );
            $this->getEntityManager()->flush();

            $data = array( 'err' => false );
            $this->AuditPlugin()->auditProject( 204, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId() );
            $this->flashMessenger()->addMessage( array(
                'The project has been re-activated successfully', 'Success!'
            ) );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    /**
     * Lost project option
     */
    public function lostAction()
    {
        try
        {

            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'Illegal request' );
            }

            $em       = $this->getEntityManager();
            $hydrator = new DoctrineHydrator( $em, 'Project\Entity\Project' );
            $hydrator->hydrate(
                array(
                    'status' => 600
                ),
                $this->getProject() );

            $em->persist( $this->getProject() );/**/
            $em->flush();
            $data = array( 'err' => false );

            // Set reason for Audit table
            // Get project lost reason
            $reason = $this->params()->fromPost( 'reason', '' );
            $reason = Array( 'data' => Array( 'reason' => $reason ) );
            $this->AuditPlugin()->auditProject( 210, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), $reason );
            $this->flashMessenger()->addMessage( array(
                'The project upgraded to lost successfully', 'Success!'
            ) );

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );
    }

    /**
     * Reactivate the lost project
     */
    public function reactivateAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $em       = $this->getEntityManager();
            $hydrator = new DoctrineHydrator( $em, 'Project\Entity\Project' );
            $hydrator->hydrate(
                array(
                    'status' => 1
                ),
                $this->getProject() );

            $em->persist( $this->getProject() );
            $em->flush();

            $data = array( 'err' => false );
            $this->AuditPlugin()->auditProject( 204, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId() );
            $this->flashMessenger()->addMessage( array(
                'The project has been re-activated successfully', 'Success!'
            ) );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );
    }

    public function signedAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                //throw new \Exception('illegal request');
            }

            $em   = $this->getEntityManager();
            $file = $this->params()->fromFiles( 'file', false );

            if ( !empty($file) )
            {
                $dsconfig        = $this->getServiceLocator()->get( 'Config' );
                $documentService = new \Project\Service\DocumentService( $dsconfig['googleApps']['drive']['location'], $em );
                $documentService
                    ->setUser( $this->getUser() )
                    ->setProject( $this->getProject() );

                $category = $em->find( 'Project\Entity\DocumentCategory', 20 );
                if ( !($category instanceof \Project\Entity\DocumentCategory) )
                {
                    throw new \Exception( 'illegal category' );
                }

                $config['category'] = $category;
                $config['route']    = array();
                if ( !empty($category->getLocation()) )
                {
                    $config['route'] = explode( '/', trim( $category->getLocation(), '/' ) );
                }

                $config['filename'] = $category->getName() . ' - ' . str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . ' - ' . preg_replace("/[^ \w]+/", "_", $this->getProject()->getName()) . '.' . preg_replace( '/^[\s\S]+[.]([^.]+)$/', '$1', $file['name'] );

                $documentService->saveUploadedFile( $file, $config );

                // Adding expected completion date of project
                /*
                $dt = null;
                if ( !empty($completionDate) )
                {
                    $arr = explode('-', $completionDate);
                    $dt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $arr[1], $arr[0], $arr[2] ) ) );
                }
                */
                $hydrator = new DoctrineHydrator( $em, 'Project\Entity\Project' );
                $hydrator->hydrate(
                    array(
                        'weighting'  => 100,
                        'status'     => 40,
                        'rating'     => 7,
                        'contracted' => new \DateTime()
                    ),
                    $this->getProject() );

                $em->persist( $this->getProject() );
                $em->flush();

                $products = $this->getEntityManager()->getRepository('Project\Entity\Project')->findProducts($this->getProject());

                if ( !empty($products) )
                {
                    $productActivity = new \Resource\Entity\ResourceActivity();
                    $dt = new \DateTime();
                    $startDt = new \DateTime();
                    $endDt = new \DateTime();
                    $resource = $this->getEntityManager()->find( 'Resource\Entity\Resource', 14 );
                    $costCode = $this->getEntityManager()->find( 'Resource\Entity\CostCode', 6 );
                    foreach( $products as $product )
                    {
                        //$product  = $this->getEntityManager()->getRepository( 'Product\Entity\Product' )->find( $product['productId'] );
                        $productActivity->setDate( $dt );
                        $productActivity->setStartDate( $startDt );
                        $productActivity->setEndDate( $endDt );
                        $status   = $this->getEntityManager()->find( 'Resource\Entity\Status', 1 );
                        $productActivity->setStatus( $status );

                        $productActivity->setCostCode( $costCode );
                        $productActivity->setResource( $resource );
                        $productActivity->setDetails( $product['model'] );
                        $productActivity->setProject( $this->getProject()->getProjectId() );
                        $productActivity->setQuantity( $product['quantity'] );
                        $productActivity->setRate( $product['cpu'] );
                        $productActivity->setReference( $product['productId'] );
                        $productActivity->setReferenceType( 'product' );
                        $this->getEntityManager()->persist( $productActivity );

                    }
                    $this->getEntityManager()->flush();
                }

                $data = array( 'err' => false, 'url' => '/client-' . $this->getProject()->getClient()->getClientId() . '/job-' . $this->getProject()->getProjectId() . '/' );
                $this->AuditPlugin()->auditProject( 205, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId() );
                $this->flashMessenger()->addMessage( array(
                    'The project upgraded to a job successfully', 'Success!'
                ) );
            }
            else
            {
                throw new \Exception( 'No files found' );
            }
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }


    public function addPropertyAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $post = $this->getRequest()->getPost();

            $form = new \Project\Form\BlueSheetOrderDateForm();
            $form->setInputFilter( new \Project\Filter\BlueSheetOrderDateFilter() );

            $form->setData( $post );
            if ( $form->isValid() )
            {
                $projectProperty = new \Project\Entity\ProjectProperty();
                $property        = $this->getEntityManager()->find( 'Application\Entity\Property', 20 ); // order date
                $dt              = \DateTime::createFromFormat( 'd/m/Y', $post['OrderDate'] );
                $projectProperty
                    ->setValue( $dt->getTimestamp() )
                    ->setProject( $this->getProject() )
                    ->setProperty( $property );


                $this->getEntityManager()->persist( $projectProperty );
                $this->getEntityManager()->flush();

                $data = array( 'err' => false );
            }
            else
            {
                $data = array( 'err' => true, 'info' => $form->getMessages() );
            }
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function competitorDeleteAction()
    {
        try
        {
            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                throw new \Exception( 'Illegal request type' );
            }

            if ( !$this->getRequest()->isPost() )
            {
                throw new \Exception( 'illegal method' );
            }

            $cid = $this->params()->fromPost( 'cid' );
            if ( empty($cid) )
            {
                throw new \Exception( 'competitor id not found' );
            }

            foreach ( $this->getProject()->getCompetitors() as $competitorLink )
            {
                if ( $competitorLink->getCompetitor()->getCompetitorId() == $cid )
                {
                    $this->getEntityManager()->remove( $competitorLink );
                }
            }

            $this->getEntityManager()->flush();

            $data = array( 'err' => false );

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function competitorsaveAction()
    {
        try
        {
            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                throw new \Exception( 'Illegal request type' );
            }

            if ( !$this->getRequest()->isPost() )
            {
                throw new \Exception( 'illegal method' );
            }

            $cid = $this->params()->fromPost( 'cid' );
            if ( empty($cid) )
            {
                throw new \Exception( 'competitor id not found' );
            }

            $competitor = $this->getEntityManager()->find( 'Application\Entity\Competitor', $cid );

            if ( empty($competitor) )
            {
                throw new \Exception( 'competitor does not exist' );
            }


            $post       = $this->params()->fromPost();
            $weaknesses = array();
            if ( !empty($post['weaknesses']) )
            {
                foreach ( $post['weaknesses'] as $weakness )
                {
                    if ( !empty($weakness) )
                    {
                        $weaknesses[] = $weakness;
                    }
                }
            }

            $strengths = array();
            if ( !empty($post['strengths']) )
            {
                foreach ( $post['strengths'] as $strength )
                {
                    if ( !empty($strength) )
                    {
                        $strengths[] = $strength;
                    }
                }
            }

            $projectCompetitor = false;
            foreach ( $this->getProject()->getCompetitors() as $competitorLink )
            {
                if ( $competitorLink->getCompetitor()->getCompetitorId() == $cid )
                {
                    $projectCompetitor = $competitorLink;
                    break;
                }
            }

            if ( empty ($projectCompetitor) )
            {
                $projectCompetitor = new \Project\Entity\ProjectCompetitor();
                $projectCompetitor
                    ->setProject( $this->getProject() )
                    ->setCompetitor( $competitor );
            }
            elseif ( !empty($post['add']) )
            { // if we have the add flag but already exists then do nothing
                throw new \Exception( 'Relationship already exists' );
            }

            $projectCompetitor
                ->setResponse( empty($post['response']) ? null : $post['response'] )
                ->setStrategy( empty($post['strategy']) ? null : $post['strategy'] )
                ->setStrengths( json_encode( $strengths ) )
                ->setWeaknesses( json_encode( $weaknesses ) );

            $this->getEntityManager()->persist( $projectCompetitor );
            $this->getEntityManager()->flush();

            $data = array( 'err' => false, 'info' => array( 'name' => $competitor->getName() ) );

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function competitorFindAction()
    {
        try
        {
            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                throw new \Exception( 'Illegal request type' );
            }

            if ( !$this->getRequest()->isPost() )
            {
                throw new \Exception( 'illegal method' );
            }


            $cid = $this->params()->fromPost( 'cid' );

            $competitor = array();
            foreach ( $this->getProject()->getCompetitors() as $competitorLink )
            {
                if ( $competitorLink->getCompetitor()->getCompetitorId() == $cid )
                {
                    $competitor = array(
                        'cid'         => $competitorLink->getCompetitor()->getCompetitorId(),
                        'name'        => $competitorLink->getCompetitor()->getName(),
                        'url'         => !empty($competitorLink->getCompetitor()->getUrl()) ? 'http://' . preg_replace( '/^http:[\/]+/i', '', $competitorLink->getCompetitor()->getUrl() ) : null,
                        'gStrengths'  => json_decode( $competitorLink->getCompetitor()->getStrengths(), true ),
                        'gWeaknesses' => json_decode( $competitorLink->getCompetitor()->getWeaknesses(), true ),
                        'strengths'   => json_decode( $competitorLink->getStrengths(), true ),
                        'weaknesses'  => json_decode( $competitorLink->getWeaknesses(), true ),
                        'strategy'    => empty($competitorLink->getStrategy()) ? '' : $competitorLink->getStrategy(),
                        'response'    => empty($competitorLink->getResponse()) ? '' : $competitorLink->getResponse(),
                    );

                    break;
                }
            }

            if ( empty ($competitor) )
            {
                throw new \Exception( 'Item not found' );
            }

            $data = array( 'err' => false, 'info' => $competitor );

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }


    public function blueSheetAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());


        if ( $saveRequest )
        {
            try
            {
                if ( !$this->getRequest()->isPost() )
                {
                    throw new \Exception( 'illegal method' );
                }

                $post  = $this->params()->fromPost();
                $props = $this->getEntityManager()->getRepository( 'Application\Entity\Property' )->findByGrouping( array( 1, 2, 4, 16 ) );


                $storedPropsLinks = array();
                foreach ( $this->getProject()->getProperties() as $propertyLink )
                {
                    $storedPropsLinks[$propertyLink->getProperty()->getName()] = $propertyLink;
                }

                $em = $this->getEntityManager();

                $keywinresult = array();
                if ( !empty($post['kwrcontactid']) )
                {
                    foreach ( $post['kwrcontactid'] as $id => $cid )
                    {
                        if ( empty($post['kwr'][$id]) )
                        {
                            continue;
                        }
                        $keywinresult[$cid] = array(
                            0 => $post['kwr'][$id],
                            1 => $post['kwrrating'][$id],
                        );
                    }
                }

                $post['BuyingInfluence'] = json_encode( $keywinresult );

                $obj = new \Project\Entity\ProjectProperty();
                $obj->setProject( $this->getProject() );
                $obj->setProperty( $prop );

                // save competitor information
                foreach ( $props as $prop )
                {
                    if ( !empty($post[$prop->getName()]) )
                    {
                        if ( isset($storedPropsLinks[$prop->getName()]) )
                        { // already exists
                            $obj = $storedPropsLinks[$prop->getName()];
                            if ( $obj->getValue() == $post[$prop->getName()] )
                            {
                                continue;
                            }
                        }
                        else
                        { // create new
                            $obj = new \Project\Entity\ProjectProperty();
                            $obj->setProject( $this->getProject() );
                            $obj->setProperty( $prop );
                        }

                        if ( is_array( $post[$prop->getName()] ) )
                        {
                            $arr = array();
                            foreach ( $post[$prop->getName()] as $value )
                            {
                                if ( !empty(trim( $value )) )
                                {
                                    $arr[] = $value;
                                }
                            }
                            if ( empty($arr) )
                            {
                                $em->remove( $obj );
                                continue;
                            }
                            else
                            {
                                $obj->setValue( json_encode( $arr ) );
                            }
                        }
                        else
                        {
                            $obj->setValue( $post[$prop->getName()] );
                        }

                        $em->persist( $obj );
                    }
                    else
                    {
                        if ( isset($storedPropsLinks[$prop->getName()]) )
                        {
                            $em->remove( $storedPropsLinks[$prop->getName()] );
                        }
                    }
                }
                $em->flush();
                $data = array( 'err' => false, );

            }
            catch ( \Exception $ex )
            {
                $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
            }

            return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
        }
        else
        {
            $contacts = $this->getProject()->getContacts();

            $props['competition'] = $this->getEntityManager()->getRepository( 'Application\Entity\Property' )->findByGrouping( 1 );
            $props['criteria']    = $this->getEntityManager()->getRepository( 'Application\Entity\Property' )->findByGrouping( 2 );

            $storedPropsLinks = array();
            foreach ( $this->getProject()->getProperties() as $propertyLink )
            {
                $storedPropsLinks[$propertyLink->getProperty()->getName()] = $propertyLink;
            }

            $this->setCaption( 'Blue Sheet' );

            $competitorList = array();
            $qb             = $this->getEntityManager()->createQueryBuilder();
            $qb
                ->select( 'c.name, c.competitorId' )
                ->from( 'Application\Entity\Competitor', 'c' );

            $query          = $qb->getQuery();
            $competitorsTmp = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
            $competitorList = array();
            foreach ( $competitorsTmp as $data )
            {
                $competitorList[$data['competitorId']] = $data;
            }

            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb
                ->select( 'c.competitorId' )
                ->from( 'Project\Entity\ProjectCompetitor', 'pc' )
                ->innerJoin( 'pc.competitor', 'c' )
                ->where( 'pc.project = ' . $this->getProject()->getProjectId() );

            $query   = $qb->getQuery();
            $exclude = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

            foreach ( $exclude as $competitor )
            {
                if ( isset($competitorList[$competitor['competitorId']]) )
                {
                    unset ($competitorList[$competitor['competitorId']]);
                }
            }

            $competitors = $this->getProject()->getCompetitors();

            $formCompetitorAdd = new \Application\Form\CompetitorAddForm( $this->getEntityManager() );
            $formCompetitorAdd
                ->setAttribute( 'action', '/competitor/add/' )
                ->setAttribute( 'class', 'form-horizontal' );

            $formOrderDate = new \Project\Form\BlueSheetOrderDateForm();
            $formOrderDate
                ->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/addproperty/' )
                ->setAttribute( 'class', 'form-horizontal' );


            if ( isset($storedPropsLinks['OrderDate']) )
            {
                $formOrderDate->get( 'OrderDate' )->setValue( date( 'd/m/Y', $storedPropsLinks['OrderDate']->getValue() ) );
            }

            $this->getView()
                ->setVariable( 'formOrderDate', $formOrderDate )
                ->setVariable( 'formCompetitorAdd', $formCompetitorAdd )
                ->setVariable( 'competitorList', $competitorList )
                ->setVariable( 'competitors', $competitors )
                ->setVariable( 'storedProps', $storedPropsLinks )
                ->setVariable( 'props', $props )
                ->setVariable( 'contacts', $contacts );

            return $this->getView();
        }
    }


    /**
     * Add new space action metho
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function newSpaceAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $post  = $this->getRequest()->getPost();
            $form  = new SpaceCreateForm( $this->getEntityManager(), $this->getProject()->getClient()->getClientId() );
            $space = new \Space\Entity\Space();
            $form->bind( $space );
            //$form->setBindOnValidate(true);

            $form->setData( $post );
            if ( $form->isValid() )
            {
                $space->setProject( $this->getProject() );
                $form->bindValues();
                $this->getEntityManager()->persist( $space );
                $this->getEntityManager()->flush();

                $this->flashMessenger()->addMessage( array(
                    'The space &quot;' . $space->getName() . '&quot; has been added successfully', 'Success!'
                ) );

                $data = array( 'err' => false, 'url' => '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/space-' . $space->getSpaceId() . '/' );
                $this->AuditPlugin()->auditSpace( 301, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), $space->getSpaceId() );
            }
            else
            {
                $data = array( 'err' => true, 'info' => $form->getMessages() );
            }
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    /**
     * Add new space action metho
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function deleteSpaceAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $spaceId = $this->params()->fromPost( 'spaceId', false );
            if ( empty($spaceId) )
            {
                throw new \Exception( 'No space ID found' );
            }

            $space = $this->getEntityManager()->find( 'Space\Entity\Space', $spaceId );
            if ( !($space instanceof \Space\Entity\Space) )
            {
                throw new \Exception( 'No space could be found' );
            }

            if ( $space->getProject()->getProjectId() != $this->getProject()->getProjectId() )
            {
                throw new \Exception( 'Space does not belong to this project' );
            }

            $em           = $this->getEntityManager();
            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder
                ->select( 'p.productId, s.systemId' )
                ->from( 'Space\Entity\System', 's' )
                ->join( 's.product', 'p' )
                ->leftjoin( 's.legacy', 'l' )
                ->where( 's.space=?1' )
                ->setParameter( 1, $spaceId );

            $query  = $queryBuilder->getQuery();
            $result = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

            $products = array();
            $systems  = array();
            if ( !empty($result) )
            {
                foreach ( $result as $systemData )
                {
                    $products[$systemData['productId']] = $systemData['productId'];
                    $systems[]                          = $systemData['systemId'];
                }
            }

            $sql  = "DELETE FROM `System` WHERE `space_id`={$spaceId}";
            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();

            $space->setDeleted( true );
            $em->persist( $space );
            $em->flush();

            $data = array( 'err' => false );
            $this->AuditPlugin()->auditSpace( 302, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), $space->getSpaceId() );

            $this->synchronizePricing( $products );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }


    /**
     * Add new space action metho
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function copySpaceAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }


            $spaceId = $this->params()->fromPost( 'spaceId', false );
            if ( empty($spaceId) )
            {
                throw new \Exception( 'No space ID found' );
            }

            $em = $this->getEntityManager();

            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder
                ->select( 's.name, s.notes, s.root, b.buildingId AS building' )
                ->from( 'Space\Entity\Space', 's' )
                ->leftJoin( 's.building', 'b' )
                ->where( 's.spaceId=?1' )
                ->andWhere( 's.project=?2' )
                ->setParameter( 1, $spaceId )
                ->setParameter( 2, $this->getProject()->getProjectId() );

            $query  = $queryBuilder->getQuery();
            $result = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

            if ( empty($result) )
            {
                throw new \Exception( 'space could not be found' );
            }

            $spaceData         = array_shift( $result );
            $spaceData['name'] = $this->params()->fromPost( 'newSpaceName', $spaceData['name'] . ' 1' );

            $space    = new \Space\Entity\Space();
            $hydrator = new DoctrineHydrator( $em, 'Space\Entity\Space' );
            $hydrator->hydrate( $spaceData, $space );
            $space->setProject( $this->getProject() );
            $em->persist( $space );


            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder
                ->select( 'p.productId AS product, l.legacyId AS legacy, s.cpu, s.ppu, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.label, s.locked, s.attributes' )
                ->from( 'Space\Entity\System', 's' )
                ->join( 's.product', 'p' )
                ->leftjoin( 's.legacy', 'l' )
                ->where( 's.space=?1' )
                ->setParameter( 1, $spaceId );

            $query    = $queryBuilder->getQuery();
            $result   = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
            $products = array();
            if ( !empty($result) )
            {
                foreach ( $result as $systemData )
                {
                    $products[$systemData['product']] = $systemData['product'];
                    $system                           = new \Space\Entity\System();
                    $hydrator                         = new DoctrineHydrator( $em, 'Space\Entity\System' );
                    $hydrator->hydrate( $systemData, $system );
                    $system->setSpace( $space );
                    $em->persist( $system );
                }/**/
            }

            $em->flush();
            $data = array( 'err' => false, 'url' => '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/space-' . $space->getSpaceId() . '/' );
            $this->AuditPlugin()->auditSpace( 301, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), $space->getSpaceId() );

            // now we need to synchronize pricing
            $products = array();
            if ( !empty($result) )
            {
                foreach ( $result as $systemData )
                {
                    $products[$systemData['product']] = $systemData['product'];
                }/**/
            }

            // synchronize product price point according to quantities
            $this->synchronizePricing( $products );

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }


    public function configRefreshAction()
    {
        try
        {
            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                throw new \Exception( 'illegal request format' );
            }


            $query = $this->getEntityManager()->createQuery( "SELECT ps.saveId, ps.name, ps.created "
                . "FROM Project\Entity\Save ps "
                . "WHERE ps.project = {$this->getProject()->getProjectId()} "
                . "ORDER BY ps.activated DESC" );

            $res   = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
            $saves = array();
            foreach ( $res as $save )
            {
                $saves[] = array(
                    $save['saveId'],
                    $save['created']->format( 'jS F Y H:i:s' ) . (empty($save['name']) ? '' : ' - ' . $save['name']) . ' - [' . $save['saveId'] . ']',

                );
            }

            // use repository to find addresses
            $data = array( 'err' => false, 'saves' => $saves );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }


    /**
     * action to load config
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function configLoadAction()
    {
        //$obj = $serializer->unserialize($y);
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }
            $saveId = $this->params()->fromPost( 'saveId', false );

            if ( !preg_match( '/^[\d]+$/', $saveId ) )
            {
                throw new \Exception( 'illegal request' );
            }
            $em   = $this->getEntityManager();
            $save = $em->find( 'Project\Entity\Save', $saveId );

            if ( !($save instanceof \Project\Entity\Save) )
            {
                throw new \Exception( 'invalid save request' );
            }

            if ( $save->getProject()->getProjectId() != $this->getProject()->getProjectId() )
            {
                throw new \Exception( 'Project does not match' );
            }

            // autosave if enabled
            $saveMe = $this->params()->fromPost( 'autoSave', false );
            if ( !empty($saveMe) )
            {
                $this->saveConfig();
            }

            // step 1: deserialize
            $serializer = \Zend\Serializer\Serializer::factory( 'phpserialize' );
            $config     = $serializer->unserialize( $save->getConfig() );

            // step 2: update project
            $hydrator = new DoctrineHydrator( $em, 'Project\Entity\Project' );
            $hydrator->hydrate(
                $config['setup'],
                $this->getProject() );

            $em->persist( $this->getProject() );/**/

            // step 3: delete current configuration
            $osystems = $em->getRepository( 'Space\Entity\System' )->findByProjectId( $this->getProject()->getProjectId() );
            if ( !empty($osystems) )
            {
                foreach ( $osystems as $osystem )
                {
                    $em->remove( $osystem );
                }
            }


            $spaces = array();
            // step 3: parse saved data
            $hydrator = new DoctrineHydrator( $em, 'Space\Entity\System' );
            foreach ( $config['system'] as $system )
            {
                $systemObj = new \Space\Entity\System();
                $hydrator->hydrate(
                    array(
                        'cpu'            => $system[0],
                        'ppu'            => $system[1],
                        'ippu'           => $system[2],
                        'quantity'       => $system[3],
                        'hours'          => $system[4],
                        'legacyWatts'    => $system[5],
                        'legacyQuantity' => $system[6],
                        'legacyMcpu'     => $system[7],
                        'lux'            => $system[8],
                        'occupancy'      => $system[9],
                        'label'          => $system[10],
                        'locked'         => $system[11],
                        'product'        => $system[12],
                        'space'          => $system[13],
                        'legacy'         => $system[14],
                        'attributes'     => $system[15],
                    ),
                    $systemObj
                );
                $em->persist( $systemObj );
                $spaces[$system[13]] = true;
            }/**/

            // step 4: ensure spaces are switched on
            $sspaces = $em->getRepository( 'Space\Entity\Space' )->findByProjectId( $this->getProject()->getProjectId() );
            if ( !empty($sspaces) )
            {
                foreach ( $sspaces as $sspace )
                {
                    if ( isset($spaces[$sspace->getSpaceId()]) )
                    {
                        if ( $sspace->getDeleted() )
                        {
                            $sspace->setDeleted( false );
                            if ( $sspace->getBuilding()->getDeleted() )
                            {
                                $sspace->getBuilding()->setDeleted( false );
                            }
                            $em->persist( $sspace );
                        }
                    }
                    elseif ( !$sspace->getRoot() )
                    {
                        if ( !$sspace->getDeleted() )
                        {
                            $sspace->setDeleted( true );
                            $em->persist( $sspace );
                        }
                    }
                }
            }

            // step 5: add new data


            // step 6: set activated date
            $save->setActivated( new \DateTime() );
            $em->persist( $save );

            // step 7: results
            $em->flush();

            // return data
            $data = array( 'err' => false );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function configSaveAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $name = $this->params()->fromPost( 'name', false );
            $name = empty($name) ? null : $name;

            // hydrate the doctrine entity
            $save = $this->saveConfig( $name );

            // return data
            $data = array(
                'err' => false, 'info' => array(
                    'saveId'  => $save->getSaveId(),
                    'name'    => $save->getName(),
                    'created' => $save->getCreated()->format( 'jS F Y H:i:s' )
                )
            );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }


    public function fileManagerUploadAction()
    {
        $storeFolder = '/Users/jonnycook/ZendProjects/projects/8point3upload/';
        if ( !empty($_FILES) )
        {
            try
            {
                $tempFile   = $_FILES['file']['tmp_name'];          //3
                $targetPath = $storeFolder;  //4
                $targetFile = $targetPath . $_FILES['file']['name'];  //5

                if ( !move_uploaded_file( $tempFile, $targetFile ) )
                {
                    throw new \Exception( 'bugger' );
                } //6/**/
            }
            catch ( \Exception $e )
            {
                $this->AuditPlugin()->auditProject( 202, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), array( 'data' => array( 'failed = ' . $e->getMessage() . ' - ' . date( 'Y-m-d H:i:s' ) ) ) );
            }
        }
        else
        {
            $result = array();

            $files = scandir( $storeFolder );                 //1
            if ( false !== $files )
            {
                foreach ( $files as $file )
                {
                    if ( '.' != $file && '..' != $file )
                    {       //2
                        $obj['name'] = $file;
                        $obj['size'] = filesize( $storeFolder . $ds . $file );
                        $result[]    = $obj;
                    }
                }
            }

            header( 'Content-type: text/json' );              //3
            header( 'Content-type: application/json' );
            echo json_encode( $result );
        }
        die();
    }

    public function fileManagerRetrieveAction()
    {
        $file        = $this->params()->fromQuery( 'file' );
        $storeFolder = '/Users/jonnycook/ZendProjects/projects/8point3upload/';
        header( $_SERVER["SERVER_PROTOCOL"] . " 200 OK" );
        header( "Cache-Control: public" ); // needed for i.e.
        header( "Content-Type: image/png" );
        header( "Content-Transfer-Encoding: Binary" );
        header( "Content-Length:" . filesize( $storeFolder . $file ) );
        readfile( $storeFolder . $file );
        die();

    }

    /**
     * Delete note from space
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function deleteNoteAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $post   = $this->getRequest()->getPost();
            $noteId = $post['nid'];

            $errs = array();
            if ( empty($noteId) )
            {
                throw new \Exception( 'note identifier not found' );
            }

            $notes = $this->getProject()->getNotes();
            $notes = json_decode( $notes, true );

            $updated = false;
            if ( !empty($post['scope']) )
            {
                if ( !empty($notes[$post['scope']][$noteId]) )
                {
                    unset($notes[$post['scope']][$noteId]);
                    $updated = true;
                }
            }
            elseif ( !empty($notes[$noteId]) )
            {
                unset($notes[$noteId]);
                $updated = true;
            }

            if ( $updated )
            {
                $notes = json_encode( $notes );
                $this->getProject()->setNotes( $notes );
                $this->getEntityManager()->persist( $this->getProject() );
                $this->getEntityManager()->flush();
            }

            $data = array( 'err' => false );

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    /**
     * Add note to project
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function addNoteAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }


            $post = $this->getRequest()->getPost();
            $note = $post['note'];
            $errs = array();
            if ( empty($note) )
            {
                $errs['note'] = array( 'Note cannot be empty' );
            }

            if ( !empty($errs) )
            {
                return new JsonModel( array( 'err' => true, 'info' => $errs ) );
            }

            $notes = $this->getProject()->getNotes();
            $notes = json_decode( $notes, true );
            if ( empty($notes) )
            {
                $notes = array();
            }

            $scope    = $post['nscope'];
            $scopeTxt = '';
            $noteIdx  = time();
            switch ( $scope )
            {
                case 2:
                    $scopeTxt                   = 'delivery';
                    $notes[$scopeTxt][$noteIdx] = $note;
                    break;
                default:
                    $notes[$noteIdx] = $note;
                    break;
            }

            $noteCnt = count( $notes );
            $notes   = json_encode( $notes );

            $this->getProject()->setNotes( $notes );
            $this->getEntityManager()->persist( $this->getProject() );
            $this->getEntityManager()->flush();

            if ( $noteCnt == 1 )
            {
                $this->flashMessenger()->addMessage( array( 'The project note has been added successfully', 'Success!' ) );
            }

            $data = array( 'err' => false, 'cnt' => $noteCnt, 'id' => $noteIdx );
            if ( !empty($scopeTxt) )
            {
                $data['scope'] = ucwords( $scopeTxt );
            }

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    function collaboratorsAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());

        $form = new \Project\Form\CollaboratorsForm( $this->getEntityManager() );
        $form
            ->setAttribute( 'class', 'form-horizontal' )
            ->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/collaborators/' );

        $form->bind( $this->getProject() );
        $form->setBindOnValidate( true );

        if ( $saveRequest )
        {
            try
            {
                if ( !$this->getRequest()->isPost() )
                {
                    throw new \Exception( 'illegal method' );
                }

                $post = $this->params()->fromPost();
                if ( empty($post['collaborators']) )
                {
                    $post['collaborators'] = array();
                }

                $hydrator = new DoctrineHydrator( $this->getEntityManager(), 'Project\Entity\Project' );
                $hydrator->hydrate( $post, $this->getProject() );

                $this->getEntityManager()->persist( $this->getProject() );
                $this->getEntityManager()->flush();

                $data = array( 'err' => false );
            }
            catch ( \Exception $ex )
            {
                $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
            }

            return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
        }
        else
        {
            $this->setCaption( 'Collaborators' );


            $this->getView()
                ->setVariable( 'form', $form );

            return $this->getView();
        }

    }

    function emailAction()
    {
        $this->setCaption( 'Project Emails' );
        $form = new \Project\Form\EmailForm();

        $recipients = array(
            'client' => array(
                'label'   => 'CLIENT CONTACTS',
                'options' => array(),
            ),
            'projis' => array(
                'label'   => 'PROJIS CONTACTS',
                'options' => array(),
            ),
        );
        $contacts   = $this->getEntityManager()->getRepository( 'Contact\Entity\Contact' )->findByClientId( $this->getProject()->getClient()->getclientId() );
        foreach ( $contacts as $contact )
        {
            if ( empty($contact->getEmail()) )
            {
                continue;
            }
            $recipients['client']['options'][$contact->getEmail()] = $contact->getForename() . ' ' . $contact->getSurname();
        }


        $users = $this->getEntityManager()->getRepository( 'Application\Entity\User' )->findByCompany( $this->getUser()->getCompany()->getCompanyId() );
        foreach ( $users as $user )
        {
            if ( empty($user->getEmail()) )
            {
                continue;
            }
            if ( !empty($recipients['client']['options'][$user->getEmail()]) )
            { // cannot have duplicate email addresses
                continue;
            }
            $recipients['projis']['options'][$user->getEmail()] = $user->getName();
        }


        $form->get( 'to' )->setAttribute( 'options', $recipients );
        $form->get( 'cc' )->setAttribute( 'options', $recipients );
        $form->get( 'message' )->setValue( '<br /><br />Regards,<br /><br />' . $this->getUser()->getName() . '<br />' . ucwords( $this->getUser()->getPosition()->getName() ) );

        $form
            ->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/emailsend/' )
            ->setAttribute( 'class', 'form-horizontal' );
        $this->getView()
            ->setVariable( 'form', $form );

        return $this->getView();
    }

    function emailThreadAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $googleService = $this->getGoogleService();

            if ( !$googleService->hasGoogle() )
            {
                die ('the service is not enabled for this user');
            }
            $googleService->setProject( $this->getProject() );
            $mail = $googleService->findGmailThreads( array(), false );

            return new JsonModel( array( 'err' => false, 'mail' => $mail ) );/**/
        }
        catch ( \Exception $e )
        {
            return new JsonModel( array( 'err' => true, 'info' => $e->getMessage() ) );/**/
        }
    }

    function emailItemAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $threadId = $this->params()->fromPost( 'threadId', false );
            if ( empty($threadId) )
            {
                throw new \Exception( 'valid thread ID not found' );
            }

            $googleService = $this->getGoogleService();

            if ( !$googleService->hasGoogle() )
            {
                die ('the service is not enabled for this user');
            }

            $googleService->setProject( $this->getProject() );

            $mail = $googleService->findGmailThread( $threadId );

            return new JsonModel( array( 'err' => false, 'mail' => $mail ) );/**/
        }
        catch ( \Exception $e )
        {
            return new JsonModel( array( 'err' => true, 'info' => $e->getMessage() ) );/**/
        }
    }

    function emailSendAction()
    {
        try
        {
            $form       = new \Project\Form\EmailForm();
            $recipients = array(
                'client' => array(
                    'label'   => 'CLIENT CONTACTS',
                    'options' => array(),
                ),
                'projis' => array(
                    'label'   => 'PROJIS CONTACTS',
                    'options' => array(),
                ),
            );
            $contacts   = $this->getEntityManager()->getRepository( 'Contact\Entity\Contact' )->findByClientId( $this->getProject()->getClient()->getclientId() );
            foreach ( $contacts as $contact )
            {
                if ( empty($contact->getEmail()) )
                {
                    continue;
                }
                $recipients['client']['options'][$contact->getEmail()] = $contact->getForename() . ' ' . $contact->getSurname();
            }


            $users = $this->getEntityManager()->getRepository( 'Application\Entity\User' )->findByCompany( $this->getUser()->getCompany()->getCompanyId() );
            foreach ( $users as $user )
            {
                if ( empty($user->getEmail()) )
                {
                    continue;
                }
                if ( !empty($recipients['client']['options'][$user->getEmail()]) )
                { // cannot have duplicate email addresses
                    continue;
                }
                $recipients['projis']['options'][$user->getEmail()] = $user->getName();
            }

            $form->get( 'to' )->setAttribute( 'options', $recipients );
            $form->get( 'cc' )->setAttribute( 'options', $recipients );

            $form->setInputFilter( new \Project\Filter\EmailFilter() );

            $post = $this->params()->fromPost();
            $form->setData( $post );

            if ( $form->isValid() )
            {
                $googleService = $this->getGoogleService();
                $googleService->setProject( $this->getProject() );

                if ( !$googleService->hasGoogle() )
                {
                    throw new \Exception ( 'account does not support emails' );
                }

                $params = array();
                $cc     = $form->get( 'cc' )->getValue();
                if ( !empty($cc) )
                {
                    $params['cc'] = $cc;
                }

                $response = $googleService->sendGmail( $form->get( 'subject' )->getValue(), $form->get( 'message' )->getValue(), $form->get( 'to' )->getValue(), $params );

                $data = array();
                if ( isset($response->id) )
                {
                    $data['id'] = $response->id;
                }

                if ( isset($response->threadId) )
                {
                    $data['threadId'] = $response->threadId;
                }

                $this->AuditPlugin()->activityProject( 10, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), $form->get( 'subject' )->getValue(), array(
                    'duration' => 5,
                    'data'     => $data,
                ) );

                $data = array( 'err' => false, 'info' => $response );
            }
            else
            {
                $data = array( 'err' => true, 'info' => $form->getMessages() );
            }

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array() : $data );/**/


    }

    public function telemetryAction()
    {
        $this->setCaption( 'Telemetry and Control Management' );

        $em = $this->getEntityManager();

        return $this->getView();
    }

    public function picklistAction()
    {
        $mode = $this->params()->fromQuery( 'mode', 0 );

        $em        = $this->getEntityManager();
        $breakdown = $this->getModelService()->spaceBreakdown( $this->getProject() );

        $architectural = array(
            //'_A'=>array (false,'A Board','PCB Boards Type A',0),
            //'_B'=>array (false,'B Board','PCB Boards Type B',0),
            //'_B1'=>array (false,'B1 Board','PCB Boards Type B1',0),
            //'_C'=>array (false,'C Board','PCB Boards Type C',0),
            '_EC'  => array( false, 'End Caps', 'Board group end caps', 0 ),
            '_ECT' => array( false, 'End Caps (Terminating)', 'Board group terminating end caps', 0 ),
            '_CBL' => array( false, '200mm Cable', '200mm black and red cable', 0 ),
            '_WG'  => array( false, 'Wago Connectors', 'Wago Connectors', 0 ),
        );

        $boards    = array();
        $phosphor  = array();
        $aluminium = array();
        $standard  = array();


        foreach ( $breakdown as $buildingId => $building )
        {
            foreach ( $building['spaces'] as $spaceId => $space )
            {
                $multiplier = empty($space['quantity']) ? 1 : (int)$space['quantity'];
                foreach ( $space['products'] as $systemId => $system )
                {

                    if ( $system[2] == 3 )
                    { // architectural
                        if ( empty($boards[$system[4]]) )
                        {
                            $boards[$system[4]] = array(
                                '_A'  => array( $system[3], 'A Board', 'PCB Boards Type A', 0 ),
                                '_B'  => array( $system[3], 'B Board', 'PCB Boards Type B', 0 ),
                                '_B1' => array( $system[3], 'B1 Board', 'PCB Boards Type B1', 0 ),
                                '_C'  => array( $system[3], 'C Board', 'PCB Boards Type C', 0 ),
                            );
                        }
                        $attributes = json_decode( $system[16], true);
                        $this->getServiceLocator()->get( 'Model' )->getPickListItems( $attributes, $boards[$system[4]], $architectural, $phosphor, $aluminium, $multiplier );
                        //$this->debug()->dump($boards, false); $this->debug()->dump($phosphor, false); $this->debug()->dump($aluminium, false); $this->debug()->dump($architectural);

                    }
                    else
                    {
                        if ( empty($standard[$system[3]]) )
                        {
                            $standard[$system[3]] = array(
                                $system[3],
                                $system[4],
                                $system[8],
                                0
                            );
                        }
                        $standard[$system[3]][3] += $system[5];

                    }

                }
            }
        }


        if ( $mode == 1 )
        {
            $filename = 'picklist ' . str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . ' ' . date( 'dmyHis' ) . '.csv';
            $data     = array( array( 'Model', 'Type', 'Dependency', 'Description', 'Sage Code', 'Length', 'Quantity', 'Board Config' ) );

            foreach ( $boards as $model => $boardConfig )
            {
                foreach ( $boardConfig as $board )
                {
                    if ( $board[3] <= 0 )
                    {
                        continue;
                    }
                    $data[] = array( '"' . $board[1] . '"', '"boards"', '"' . $model . '"', '"' . $board[2] . ' for ' . $model . '"', $board[0], '', $board[3], '', );
                }
            }/**/

            foreach ( $architectural as $product )
            {
                $data[] = array( '"' . $product[1] . '"', '"components"', '', '"' . $product[2] . '"', $product[0], '', $product[3], '', );
            }/**/

            foreach ( $phosphor as $len => $cfg )
            {
                foreach ( $cfg as $brds => $qtty )
                {
                    $data[] = array( '"' . number_format( $len, 2, '.', '' ) . 'mm Remote Phosphor"', '"phosphor"', '', '"' . number_format( $len, 2, '.', '' ) . 'mm Remote Phosphor Length"', '', $len, ($qtty[0] + $qtty[1]), $brds, );
                }
            }/**/

            foreach ( $aluminium as $len => $cfg )
            {
                foreach ( $cfg as $brds => $qtty )
                {
                    $data[] = array( '"' . number_format( $len, 2, '.', '' ) . 'mm Aluminium"', '"aluminium"', '', '"' . number_format( $len, 2, '.', '' ) . 'mm Aluminium Length"', '', $len, $qtty, '' );
                }
            }/**/

            foreach ( $standard as $product )
            {
                $data[] = array( '"' . $product[1] . '"', '"product"', '', '"' . $product[2] . '"', $product[0], '', $product[3], '', );
            }


            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->setCaption( 'Bill Of Materials' );
        //$this->debug()->dump($boards);

        $this->getView()
            ->setVariable( 'boards', $boards )
            ->setVariable( 'standard', $standard )
            ->setVariable( 'phosphor', $phosphor )
            ->setVariable( 'aluminium', $aluminium )
            ->setVariable( 'architectural', $architectural );

        return $this->getView();

    }

    // Code by Jaidev
    /**
     * Process ajax request for status update
     *
     * @throws \Exception
     */
    public function updateStatusAction()
    {
        if ( !($this->getRequest()->isXmlHttpRequest()) )
        {
            throw new \Exception( 'illegal request' );
        }

        $rating       = $this->params()->fromPost( 'sltProjectStatus' );
        $exclude      = $this->params()->fromPost( 'exclude_from_reporting', 0 );
        $expectedDate = $this->params()->fromPost( 'expected_date', null );
        $dateArr      = explode( '/', $expectedDate );

        if ( in_array( $rating, Array( 1, 2, 3 ) ) && empty($expectedDate) )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => 'You must add an Expected Date for a rated project!' ) );

            return new JsonModel( $data );
        }

        if ( in_array( $rating, Array( 1, 2, 3 ) ) && $exclude )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => 'You cannot exclude a rated project!' ) );

            return new JsonModel( $data );
        }

        if ( in_array( $rating, array( 0, 1, 2, 3 ) ) )
        {
            $this->getProject()->setRating( $rating );
            $excludeReporting = empty($exclude) ? 0 : $exclude;
            $this->getProject()->setExcludeReporting( $excludeReporting );
            if ( !empty($expectedDate) )
            {
                $dt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2] ) ) );
                $this->getProject()->setExpectedDate( $dt );
            }
            else
            {
                $this->getProject()->setExpectedDate( null );
            }
            $this->getEntityManager()->persist( $this->getProject() );
            $this->getEntityManager()->flush();
            $data = array( 'err' => false, 'info' => '' );
        }
        else
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => 'Invalid rating option' ) );
        }

        return new JsonModel( $data );
    }

}