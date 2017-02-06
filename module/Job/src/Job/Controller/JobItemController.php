<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Job\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User, Application\Entity\Address, Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class JobitemController extends JobSpecificController
{

    public function indexAction()
    {
        $this->setCaption( 'Job Dashboard' );

        $em       = $this->getEntityManager();
        $system = $this->getModelService()->billitems($this->getProject());

        $query     = $em->createQuery( 'SELECT count(d) '
            . 'FROM Project\Entity\DocumentList d '
            . 'WHERE '
            . 'd.project=' . $this->getProject()->getProjectId() . ' AND '
            . 'd.category IN (1, 2, 3)'
        );
        $proposals = $query->getSingleScalarResult();

        $query         = $em->createQuery( 'SELECT count(d) '
            . 'FROM Job\Entity\Dispatch d '
            . 'WHERE '
            . 'd.project=' . $this->getProject()->getProjectId() . ' AND '
            . 'd.revoked = false'
        );
        $dispatchNotes = $query->getSingleScalarResult();

        $audit = $em->getRepository( 'Application\Entity\Audit' )->findByProjectId( $this->getProject()->getProjectId(), true, array(
            'max'  => 8,
            'auto' => true,
        ) );

        $activities = $em->getRepository( 'Application\Entity\Activity' )->findByProjectId( $this->getProject()->getProjectId(), true, array(
            'max'  => 8,
            'auto' => true,
        ) );

        $query       = $em->createQuery( 'SELECT count(s) FROM Job\Entity\Serial s WHERE s.project =' . $this->getProject()->getProjectId() );
        $serialCount = $query->getSingleScalarResult();

        $formActivity = new \Application\Form\ActivityAddForm( $em, array(
            'projectId' => $this->getProject()->getProjectId(),
        ) );

        $formActivity
            ->setAttribute( 'action', '/activity/add/' )
            ->setAttribute( 'class', 'form-nomargin' );

        $contacts = $this->getProject()->getContacts();

        $payback = $this->getModelService()->payback( $this->getProject() );

        // Fetch Resource Activities and calculate their total

        $resources          = $em->getRepository( '\Resource\Entity\ResourceActivity' )->findBy( [ 'project' => $this->getProject()->getProjectId() ] );
        $totals             = [ ];
        $totals['invoices'] = 0;
        $totals['others']   = 0;

        foreach ( $resources as $resource )
        {
            $totals['quantity'] += $resource->getQuantity();
            $totals['rate'] += $resource->getRate();
            $totals['total'] += $resource->getQuantity() * $resource->getRate();
            if ( $resource->getResource()->getName() == 'Invoice' )
            {
                $totals['invoices'] += ($resource->getRate() * $resource->getQuantity());
            }
            else
            {
                $totals['others'] += $resource->getQuantity() * $resource->getRate();
            }
        }

        $this->getView()
            ->setVariable( 'dispatchNotes', $dispatchNotes )
            ->setVariable( 'serialCount', $serialCount )
            ->setVariable( 'contacts', $contacts )
            ->setVariable( 'proposals', $proposals )
            ->setVariable( 'formActivity', $formActivity )
            ->setVariable( 'figures', $payback['figures'] )
            ->setVariable( 'user', $this->getUser() )
            ->setVariable( 'audit', $audit )
            ->setVariable( 'activities', $activities )
            ->setVariable( 'system', $system )
            ->setVariable( 'totals', $totals );

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
                                '_B1PP' => array( $system[3], 'B1PP Board', 'PCB Boards Type B1PP', 0 ),
                                '_B1SP' => array( $system[3], 'B1SP Board', 'PCB Boards Type B1SP', 0 ),
                                '_C'  => array( $system[3], 'C Board', 'PCB Boards Type C', 0 ),
                            );
                        }
                        $attributes = json_decode( $system[16], true );
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

    public function buildsheetAction()
    {
        $mode = $this->params()->fromQuery( 'mode', 0 );

        $em        = $this->getEntityManager();
        $breakdown = $this->getModelService()->spaceBreakdown( $this->getProject() );

        $boards    = array();
        $phosphor  = array();
        $aluminium = array();
        $standard  = array();

        $build        = array();
        $stringConfig = array();

        foreach ( $breakdown as $buildingId => $building )
        {
            foreach ( $building['spaces'] as $spaceId => $space )
            {
                foreach ( $space['products'] as $systemId => $system )
                {

                    if ( $system[2] == 3 )
                    { // architectural
                        $attributes = json_decode( $system[16], true );

                        $this->getServiceLocator()->get( 'Model' )->getBuildsheetItems( $attributes, $system[4], $build, $stringConfig );

                    }

                }
            }
        }

        $filename = 'buildsheet ' . str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . ' ' . date( 'dmyHis' ) . '.csv';
        $data     = array( array( '"Configuration"', '"Model"', '"Quantity"', '"Phosphor Length"', '"Aluminium Length"', '"End Cap"', '"End Cap (Terminating)"', '"A"', '"B"', '"B1"', '"B1PP"', '"B1SP"', '"C"', '"WAGO"', '"Black/Red Wire"' ) );

        //$this->debug()->dump($stringConfig, false);
        //$this->debug()->dump($build);
        foreach ( $build as $model => $lengths )
        {
            foreach ( $lengths as $length => $configs )
            {
                foreach ( $configs as $config => $setup )
                {
                    $hasCBoard = preg_match( '/[-][c]$/i', $config );
                    foreach ( $setup as $sType => $qty )
                    {
                        if ( empty($qty) )
                            continue;
                        $data[] = array(
                            '"' . $config . '"',
                            '"' . $model . '"',
                            $qty,
                            $length,
                            ($length + 1),
                            (2 - $sType),
                            ($sType),
                            $stringConfig[$config]['_A'],
                            $stringConfig[$config]['_B'],
                            $stringConfig[$config]['_B1'],
                            $stringConfig[$config]['_B1PP'],
                            $stringConfig[$config]['_B1SP'],
                            $stringConfig[$config]['_C'],
                            $stringConfig[$config]['_WG'],
                            $stringConfig[$config]['_CBL'] - ($hasCBoard ? $sType : 0),
                        );
                    }
                }
            }
        }

        //$this->debug()->dump($data);


        $response = $this->prepareCSVResponse( $data, $filename );

        return $response;

    }


    public function serialsAction()
    {
        $this->setCaption( 'Serial Management' );


        $em   = $this->getEntityManager();
        $form = new \Job\Form\SerialForm( $em, $this->getProject() );
        $form->setAttribute( 'class', 'form-horizontal' );
        $form->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/job-' . $this->getProject()->getProjectId() . '/serialadd/' );


        $this->getView()
            ->setVariable( 'form', $form );

        return $this->getView();
    }

    public function seriallistAction()
    {
        $em      = $this->getEntityManager();
        $length  = $this->params()->fromQuery( 'iDisplayLength', 10 );
        $start   = $this->params()->fromQuery( 'iDisplayStart', 1 );
        $keyword = $this->params()->fromQuery( 'sSearch', '' );
        $params  = array(
            'keyword' => trim( $keyword ),
            'orderBy' => array()
        );

        $orderBy = array(
            0 => 'serialId',
            1 => 'productId',
            2 => 'spaceId'
        );
        for ( $i = 0; $i < intval( $this->params()->fromQuery( 'iSortingCols', 0 ) ); $i++ )
        {
            $j = $this->params()->fromQuery( 'iSortCol_' . $i );
            if ( $this->params()->fromQuery( 'bSortable_' . $j, false ) == "true" )
            {
                $dir = $this->params()->fromQuery( 'sSortDir_' . $i, 'ASC' );
                if ( isset($orderBy[$j]) )
                {
                    $params['orderBy'][$orderBy[$j]] = $dir;
                }
            }/**/
        }

        $paginator = $em->getRepository( 'Job\Entity\Serial' )->findPaginateByProjectId( $this->getProject()->getProjectId(), $length, $start, $params );

        $data = array(
            "sEcho"                => intval( $this->params()->fromQuery( 'sEcho', false ) ),
            "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
            "iTotalRecords"        => $paginator->getcurrentItemCount(),
            "aaData"               => array()
        );/**/

        foreach ( $paginator as $page )
        {
            $data['aaData'][] = array(
                str_pad( $page->getSerialId(), 8, "0", STR_PAD_LEFT ),
                !empty($page->getSystem()) ? $page->getSystem()->getProduct()->getModel() : 'Not specified',
                !empty($page->getSystem()) ? $page->getSystem()->getSpace()->getName() : 'Not specified',
                !empty($page->getSystem()) ? 'Linked' : 'Not Linked',
                $page->getCreated()->format( 'd/m/Y H:i' ),
            );
        }

        return new JsonModel( $data );/**/
    }

    function serialAddAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception( 'illegal request' );
            }

            $post = $this->params()->fromPost();
            $em   = $this->getEntityManager();
            $form = new \Job\Form\SerialForm( $em, $this->getProject() );
            $form->setInputFilter( new \Job\Filter\SerialFilter() );
            $form->setData( $post );
            if ( $form->isValid() )
            {
                $serialStart = $form->get( 'serialStart' )->getValue();
                $serialEnd   = $serialStart + ($form->get( 'range' )->getValue() - 1);
                $query       = $em->createQuery( 'SELECT count(s) FROM Job\Entity\Serial s WHERE s.serialId >=' . $serialStart . ' AND s.serialId <= ' . $serialEnd );
                $serialCount = $query->getSingleScalarResult();
                if ( $serialCount > 0 )
                {
                    throw new \Exception( $serialCount . ' of the serials in the specified range are already assigned to projects' );
                }

                if ( !empty($post['systemId']) )
                {
                    $system = $this->getEntityManager()->find( 'Space\Entity\System', $post['systemId'] );
                    if ( !($system instanceof \Space\Entity\System) )
                    {
                        throw \Exception( 'System is invalid' );
                    }
                }
                else
                {
                    $space = null;
                }


                for ( $i = $serialStart; $i <= $serialEnd; $i++ )
                {
                    $serial = new \Job\Entity\Serial();
                    $serial
                        ->setSerialId( $i )
                        ->setProject( $this->getProject() )
                        ->setSystem( $system );
                    $em->persist( $serial );

                }

                $em->flush();

                $data = array( 'err' => false );
                $this->AuditPlugin()->auditProject( 250, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), array( 'data' => array( $serialStart, $form->get( 'range' )->getValue() ) ) );
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
     * Edit note to project
     *
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
                $this->flashMessenger()->addMessage( array( 'The note has been added successfully to trial', 'Success!' ) );
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

    /**
     * Add note to project
     * @return \Zend\View\Model\JsonModel
     * @throws \Exception
     */
    public function editNoteAction()
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
            $noteIdx  = $post['note_idx'];
            switch ( $scope )
            {
                case 2:
                    $scopeTxt = 'delivery';
                    if ( in_array( $noteIdx, array_keys( $notes ) ) )
                    {
                        unset($notes[$noteIdx]);
                    }
                    $notes[$scopeTxt][$noteIdx] = $note;
                    break;
                default:
                    if ( in_array( $noteIdx, array_keys( $notes['delivery'] ) ) )
                    {
                        unset($notes['delivery'][$noteIdx]);
                    }
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
                $this->flashMessenger()->addMessage( array( 'The note has been added successfully to trial', 'Success!' ) );
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

    public function telemetryAction()
    {
        $this->setCaption( 'Trial Telemetry and Control Management' );

        $em = $this->getEntityManager();

        return $this->getView();
    }

    function collaboratorsAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());

        $form = new \Project\Form\CollaboratorsForm( $this->getEntityManager() );
        $form
            ->setAttribute( 'class', 'form-horizontal' )
            ->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/job-' . $this->getProject()->getProjectId() . '/collaborators/' );

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

    public function setupAction()
    {
        $saveRequest = ($this->getRequest()->isXmlHttpRequest());

        $this->setCaption( 'Job Configuration' );

        return $this->getView();
    }

    public function systemAction()
    {
        $this->setCaption( 'System Setup' );
        $breakdown = $this->getModelService()->spaceBreakdown( $this->getProject() );

        $this->getView()
            ->setVariable( 'breakdown', $breakdown );/**/

        return $this->getView();
    }

    public function modelAction()
    {
        $this->setCaption( 'Project Model' );
        $service = $this->getModelService()->payback( $this->getProject() );

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


    public function deliverynoteAction()
    {
        $this->setCaption( 'Delivery Notes' );


        $em           = $this->getEntityManager();
        $query        = $em->createQuery( 'SELECT SUM(dp.quantity) AS Quantity, p.productId '
            . 'FROM Job\Entity\DispatchProduct dp '
            . 'JOIN dp.dispatch d '
            . 'JOIN dp.product p '
            . 'WHERE d.project = ' . $this->getProject()->getProjectId() . ' '
            . 'GROUP BY p.productId'
        );
        $existingConf = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
        $existing     = array();
        foreach ( $existingConf as $prodQuantity )
        {
            $existing[$prodQuantity['productId']] = $prodQuantity['Quantity'];
        }

        $breakdown = $this->getModelService()->billitems( $this->getProject(), array( 'products' => true ) );
        //$this->debug()->dump($breakdown);

        $form = new \Job\Form\DeliveryNoteForm( $em, $this->getProject() );
        $form
            ->setAttribute( 'class', 'form-horizontal' )
            ->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/document/deliverynotegenerate/' );

        $this->getView()
            ->setVariable( 'existing', $existing )
            ->setVariable( 'breakdown', $breakdown )
            ->setVariable( 'form', $form );

        return $this->getView();
    }

    public function deliverynotelistAction()
    {
        if ( !$this->getRequest()->isXmlHttpRequest() )
        {
            //throw new \Exception('illegal request format');
        }
        $em      = $this->getEntityManager();
        $length  = $this->params()->fromQuery( 'iDisplayLength', 10 );
        $start   = $this->params()->fromQuery( 'iDisplayStart', 1 );
        $keyword = $this->params()->fromQuery( 'sSearch', '' );
        $params  = array(
            'keyword' => trim( $keyword ),
            'orderBy' => array()
        );

        $orderBy = array(
            0 => 'id',
            1 => 'postcode',
            2 => 'reference',
            3 => 'created',
            4 => 'sent',
            5 => 'owner'
        );
        for ( $i = 0; $i < intval( $this->params()->fromQuery( 'iSortingCols', 0 ) ); $i++ )
        {
            $j = $this->params()->fromQuery( 'iSortCol_' . $i );
            if ( $this->params()->fromQuery( 'bSortable_' . $j, false ) == "true" )
            {
                $dir = $this->params()->fromQuery( 'sSortDir_' . $i, 'ASC' );
                if ( isset($orderBy[$j]) )
                {
                    $params['orderBy'][$orderBy[$j]] = $dir;
                }
            }/**/
        }

        $paginator = $em->getRepository( 'Job\Entity\Dispatch' )->findPaginateByProjectId( $this->getProject()->getprojectId(), $length, $start, $params );

        $data = array(
            "sEcho"                => intval( $this->params()->fromQuery( 'sEcho', false ) ),
            "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
            "iTotalRecords"        => $paginator->getcurrentItemCount(),
            "aaData"               => array()
        );/**/

        foreach ( $paginator as $page )
        {
            $data['aaData'][] = array(
                str_pad( $page->getDispatchId(), 5, "0", STR_PAD_LEFT ),
                ($page->getAddress() instanceof \Contact\Entity\Address) ? $page->getAddress()->assemble( ', ' ) : 'Pick-Up',
                $page->getReference(),
                $page->getCreated()->format( 'd/m/Y H:i' ),
                $page->getSent()->format( 'd/m/Y' ),
                $page->getUser()->getForename() . ' ' . $page->getUser()->getSurname(),
                '<button class="btn btn-primary action-download" data-dispatchId="' . $page->getDispatchId() . '" ><i class="icon-download-alt"></i></button>',
            );
        }


        return new JsonModel( $data );/**/
    }


    public function documentAction()
    {
        $this->setCaption( 'Document Generator' );
        $bitwise   = '(BIT_AND(d.compatibility, 32)=32)';
        $query     = $this->getEntityManager()->createQuery( 'SELECT d.documentCategoryId, d.name, d.description, d.config, d.partial, d.grouping FROM Project\Entity\DocumentCategory d WHERE d.active = true AND ' . $bitwise . ' ORDER BY d.grouping' );
        $documents = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $formEmail = new \Project\Form\DocumentEmailForm( $this->getEntityManager() );

        $this->getView()
            ->setVariable( 'formEmail', $formEmail )
            ->setVariable( 'documents', $documents )
            ->setTemplate( 'project/projectitemdocument/index.phtml' );

        return $this->getView();
    }

    public function viewerAction()
    {
        $this->setCaption( 'Document Viewer' );
        // Note: we use bitwise comparison on the compatibility field: (1=project, 2=job, 4=post survey project, 8=images, 16=generated)
        $query              = $this->getEntityManager()->createQuery( 'SELECT d.documentCategoryId, d.name, d.description, d.location FROM Project\Entity\DocumentCategory d '
            . 'WHERE d.active = true AND BIT_AND(d.compatibility, 1)=1 AND d.location!=\'\' '
            . 'ORDER BY d.location' );
        $documentCategories = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $query           = $this->getEntityManager()->createQuery( 'SELECT d.documentCategoryId, d.name, d.description, d.location FROM Project\Entity\DocumentCategory d '
            . 'WHERE d.active = true AND BIT_AND(d.compatibility, 8)=8 AND d.location!=\'\' ' );
        $imageCategories = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $query             = $this->getEntityManager()->createQuery( 'SELECT d.documentCategoryId, d.name, d.description, d.location FROM Project\Entity\DocumentCategory d '
            . 'WHERE d.active = true AND BIT_AND(d.compatibility, 32)=32 AND d.location!=\'\' ' );
        $accountCategories = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );


        $this->getView()
            ->setVariable( 'accountCategories', $accountCategories )
            ->setVariable( 'documentCategories', $documentCategories )
            ->setVariable( 'imageCategories', $imageCategories )
            ->setTemplate( 'project/projectitemdocument/viewer.phtml' );

        return $this->getView();
    }

    public function explorerAction()
    {
        $this->setCaption( 'Document Viewer' );
        // Note: we use bitwise comparison on the compatibility field: (1=project, 2=job, 4=post survey project, 8=images, 16=generated)
        $query              = $this->getEntityManager()->createQuery( 'SELECT d.documentCategoryId, d.name, d.description, d.location FROM Project\Entity\DocumentCategory d '
            . 'WHERE d.active = true AND BIT_AND(d.compatibility, 1)=1 AND d.location!=\'\' '
            . 'ORDER BY d.location' );
        $documentCategories = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $query           = $this->getEntityManager()->createQuery( 'SELECT d.documentCategoryId, d.name, d.description, d.location FROM Project\Entity\DocumentCategory d '
            . 'WHERE d.active = true AND BIT_AND(d.compatibility, 8)=8 AND d.location!=\'\' ' );
        $imageCategories = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );


        $this->getView()
            ->setVariable( 'documentCategories', $documentCategories )
            ->setVariable( 'imageCategories', $imageCategories )
            ->setTemplate( 'project/projectitemdocument/explorer.phtml' );

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
                'The job has been cancelled', 'Success!'
            ) );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

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
                'The job has been re-activated successfully', 'Success!'
            ) );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function rollbackAction()
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
                    'weighting' => 50,
                    'status'    => 1,
                ),
                $this->getProject() );

            $em->persist( $this->getProject() );/**/
            $em->flush();


            $this->getEntityManager()->persist( $this->getProject() );
            $this->getEntityManager()->flush();

            $data = array( 'err' => false );
            $this->AuditPlugin()->auditProject( 204, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId() );
            $this->flashMessenger()->addMessage( array(
                'The job has been rolled back to a project successfully', 'Success!'
            ) );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }

    public function exportAction()
    {
        $this->setCaption( 'Job Export Wizard' );

        $breakdown = $this->getModelService()->spaceBreakdown( $this->getProject() );
        //$this->debug()->dump($breakdown);
        $form = new \Project\Form\ExportProjectForm();
        $form
            ->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/project-' . $this->getProject()->getProjectId() . '/export/createproject/' )
            ->setAttribute( 'class', 'form-horizontal' );
        $this->getView()
            ->setVariable( 'form', $form )
            ->setVariable( 'breakdown', $breakdown );/**/

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

        $rating        = $this->params()->fromPost( 'sltJobStatus' );
        $exclude       = $this->params()->fromPost( 'exclude_from_reporting', 0 );
        $completedDate = $this->params()->fromPost( 'completed_date', null );
        $startedDate   = $this->params()->fromPost( 'start_date', null );

        if ( in_array( $rating, array( 7, 8, 9 ) ) )
        {
            $this->getProject()->setRating( $rating );
            $excludeReporting = empty($exclude) ? 0 : $exclude;
            $this->getProject()->setExcludeReporting( $excludeReporting );

            $dateArr = explode( '/', $completedDate );
            if ( !empty($completedDate) )
            {
                $dt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2] ) ) );
                $this->getProject()->setCompleted( $dt );
            }
            else
            {
                $this->getProject()->setCompleted( null );
            }

            $dateArr = explode( '/', $startedDate );
            if ( !empty($startedDate) )
            {
                $dt = new \DateTime( date( 'm/d/Y', mktime( 1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2] ) ) );
                $this->getProject()->setContracted( $dt );
            }
            else
            {
                $this->getProject()->setContracted( null );
            }

            $this->getEntityManager()->persist( $this->getProject() );
            $this->getEntityManager()->flush();
            $data = array( 'err' => false, 'info' => '' );
        }
        else
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => 'Invalid job status' ) );
        }

        return new JsonModel( $data );
    }

    public function resourcesAction()
    {
        $this->setCaption( 'Jobs - Resource Activities' );
        $em   = $this->getEntityManager();
        $form = new \Resource\Form\ResourceActivityForm( $em, $this->getProject() );
        $form->setAttribute( 'class', 'form-horizontal' );
        $form->setAttribute( 'action', '/client-' . $this->getProject()->getClient()->getClientId() . '/job-' . $this->getProject()->getProjectId() . '/resourceadd/' );
        $job                = $this->getProject();
        $resources          = $em->getRepository( '\Resource\Entity\ResourceActivity' )->findBy( [ 'project' => $this->getProject()->getProjectId() ] );
        $totals             = [ ];
        $totals['invoices'] = 0;
        $totals['others']   = 0;

        foreach ( $resources as $resource )
        {
            $totals['quantity'] += $resource->getQuantity();
            $totals['rate'] += $resource->getRate();
            $totals['total'] += $resource->getQuantity() * $resource->getRate();
            if ( $resource->getResource()->getName() == 'Invoice' )
            {
                $totals['invoices'] += ($resource->getRate() * $resource->getQuantity());
            }
            else
            {
                $totals['others'] += $resource->getQuantity() * $resource->getRate();
            }
        }

        $resources         = $this->getEntityManager()->getRepository( '\Resource\Entity\Resource' )->findResources();
        $data['resources'] = Array();
        if ( !empty($resources) )
        {
            foreach ( $resources as $resource )
            {
                $data['resources'][$resource->getResourceId()] = $resource->getName();
            }
        }

        $costCodes         = $this->getEntityManager()->getRepository( '\Resource\Entity\CostCode' )->findCostCodes();
        $data['costcodes'] = Array();
        if ( !empty($costCodes) )
        {
            foreach ( $costCodes as $cost )
            {
                $data['costcodes'][$cost->getCostCodeId()] = $cost->getName();
            }
        }

        // Resource Activity information for modal window
        $formResourceActivity = new \Resource\Form\ResourceActivityForm( $em );
        $url                  = '/resource-activities/add';
        $formResourceActivity->setAttribute( 'action', $url )
            ->setAttribute( 'class', 'form-horizontal' );

        $status = $this->getEntityManager()->getRepository( '\Resource\Entity\Status' )->findBy( [ ], array( 'name' => 'asc' ) );

        $this->getView()
            ->setVariable( 'form', $form )
            ->setVariable( 'totals', $totals )
            ->setVariable( 'resource_form', $formResourceActivity )
            ->setVariable( 'data', $data )
            ->setVariable( 'status', $status );

        return $this->getView();
    }

    public function resourceListAction()
    {
        $em      = $this->getEntityManager();
        $length  = $this->params()->fromQuery( 'iDisplayLength', 10 );
        $start   = $this->params()->fromQuery( 'iDisplayStart', 1 );
        $keyword = $this->params()->fromQuery( 'sSearch', '' );
        $params  = array(
            'keyword' => trim( $keyword ),
            'orderBy' => array()
        );

        $orderBy = array(
            0 => 'serialId',
            1 => 'productId',
            2 => 'spaceId'
        );

        for ( $i = 0; $i < intval( $this->params()->fromQuery( 'iSortingCols', 0 ) ); $i++ )
        {
            $j = $this->params()->fromQuery( 'iSortCol_' . $i );
            if ( $this->params()->fromQuery( 'bSortable_' . $j, false ) == "true" )
            {
                $dir = $this->params()->fromQuery( 'sSortDir_' . $i, 'ASC' );
                if ( isset($orderBy[$j]) )
                {
                    $params['orderBy'][$orderBy[$j]] = $dir;
                }
            }
        }

        $project = $this->getProject();

        //$paginator = $em->getRepository( '\Resource\Entity\ResourceActivity' )->findBy( [ 'project' => $project->getProjectId() ] );

        $paginator = $em->getRepository( '\Resource\Entity\ResourceActivity' )->findResources( $project->getProjectId() );


        $data = array(
            'aaData' => array()
        );

        foreach ( $paginator as $page )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $page->getProject() );

            $link = '';
            if ( $page->getReferenceType() == 'product' )
            {
                $link = '<button data-id="' . $page->getResourceActivityId() . '" class="btn btn-primary btn-edit-product" pid="' . $project->getProjectId() . '" cid="' . $project->getClient()->getClientId() . '" rid="' . $page->getResourceActivityId() . '" ><i
                class="icon-pencil"></i></button>';
            }
            elseif ( $page->getReferenceType() == 'invoice' )
            {
                $link = '<button data-id="' . $page->getResourceActivityId() . '" class="btn btn-primary btn-edit-invoice" pid="' . $project->getProjectId() . '" cid="' . $project->getClient()->getClientId() . '" rid="' . $page->getResourceActivityId() . '" ><i class="icon-pencil"></i></button>';
            }
            elseif ( $page->getReferenceType() == 'resource' )
            {
                $link = '<button data-id="' . $page->getResourceActivityId() . '" class="btn btn-primary btn-edit-activity" pid="' . $project->getProjectId() . '" cid="' . $project->getClient()->getClientId() . '" rid="' .
                    $page->getResourceActivityId() . '" ><i class="icon-pencil"></i></button>';
            }

            $link .= ' <button data-id="' . $page->getResourceActivityId() . '" class="btn btn-danger btn-remove-invoice" pid="' . $project->getProjectId() . '" cid="' . $project->getClient()->getClientId() . '" rid="' .
                $page->getResourceActivityId() . '" ><i class="icon-remove"></i></button>';


            $data['aaData'][] = array(
                $page->getDate()->format( 'd/m/Y' ),
                //        $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName(),
                $page->getReference(),
                $page->getDetails(),
                $page->getResource()->getName(),
                $page->getCostCode()->getName(),
                '<span style="text-align:center;">' . $page->getQuantity() . '</span>',
                $page->getRate(),
                '&#163;' . number_format( ($page->getRate() * $page->getQuantity()), 2 ),
                $link,
            );
        }

        return new JsonModel( $data );/**/
    }

    public function invoiceListAction()
    {
        $em      = $this->getEntityManager();
        $length  = $this->params()->fromQuery( 'iDisplayLength', 10 );
        $start   = $this->params()->fromQuery( 'iDisplayStart', 1 );
        $keyword = $this->params()->fromQuery( 'sSearch', '' );
        $params  = array(
            'keyword' => trim( $keyword ),
            'orderBy' => array()
        );

        $orderBy = array(
            0 => 'serialId',
            1 => 'productId',
            2 => 'spaceId'
        );
        for ( $i = 0; $i < intval( $this->params()->fromQuery( 'iSortingCols', 0 ) ); $i++ )
        {
            $j = $this->params()->fromQuery( 'iSortCol_' . $i );
            if ( $this->params()->fromQuery( 'bSortable_' . $j, false ) == "true" )
            {
                $dir = $this->params()->fromQuery( 'sSortDir_' . $i, 'ASC' );
                if ( isset($orderBy[$j]) )
                {
                    $params['orderBy'][$orderBy[$j]] = $dir;
                }
            }
        }

        $project = $this->getProject();

        $paginator = $em->getRepository( '\Resource\Entity\ResourceActivity' )->findInvoices( $project->getProjectId() );

        $data = array(
            'aaData' => array()
        );

        foreach ( $paginator as $page )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $page->getProject() );

            $link = '<button data-id="' . $page->getResourceActivityId() . '" class="btn btn-primary btn-edit-invoice" pid="' . $project->getProjectId() . '" cid="' . $project->getClient()->getClientId() . '" rid="' .
                $page->getResourceActivityId() . '" ><i class="icon-pencil"></i></button>';

            $link .= ' <button data-id="' . $page->getResourceActivityId() . '" class="btn btn-danger btn-remove-invoice" pid="' . $project->getProjectId() . '" cid="' . $project->getClient()->getClientId() . '" rid="' .
                $page->getResourceActivityId() . '" ><i class="icon-remove"></i></button>';

            $data['aaData'][] = array(
                $page->getDate()->format( 'd/m/Y' ),
                $page->getReference(),
                $page->getDetails(),
                '&#163; ' . $page->getRate(),
                $link,
            );
        }

        return new JsonModel( $data );/**/
    }
}
