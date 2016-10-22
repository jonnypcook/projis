<?php
namespace Project\Controller;

use Doctrine\Common\Annotations\AnnotationException;
use Zend\Filter\Null;
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

use DOMPDFModule\View\Model\PdfModel;

use Project\Service\DocumentService;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ProjectitemdocumentController extends ProjectSpecificController
{

    public function __construct( DocumentService $ds )
    {
        parent::__construct();
        $this->setDocumentService( $ds );
    }

    public function onDispatch( MvcEvent $e )
    {
        $this->ignoreStatusRedirects = true;

        return parent::onDispatch( $e );
    }


    public function indexAction()
    {
        $this->setCaption( 'Document Generator' );
        //echo '<pre>';   print_r($contacts);echo '</pre>';
        /*
         * Compatibility:
         * 1   = project
         * 2   = job
         * 4   = post-survey project
         * 8   = images
         * 16  = generated
         * 32  = Job
         * 64  = Trial
         */

        if ( ($this->getProject()->getType()->getTypeId() == 3) )
        { // TRIAL
            $bitwise = '(BIT_AND(d.compatibility, 64)=64)';
        }
        elseif ( ($this->getProject()->getStatus()->getJob() == 1) || (($this->getProject()->getStatus()->getWeighting() >= 1) && ($this->getProject()->getStatus()->getHalt() == 1)) )
        { // JOB
            $bitwise = '(BIT_AND(d.compatibility, 32)=32)';
        }
        else
        { // PROJECT
            $bitwise = '(BIT_AND(d.compatibility, 1)=1 ' . ($this->getProject()->hasState( 10 ) ? 'OR BIT_AND(d.compatibility, 4)=4' : '') . ')';
        }

        $query     = $this->getEntityManager()->createQuery( 'SELECT d.documentCategoryId, d.name, d.description, d.config, d.partial, d.grouping FROM Project\Entity\DocumentCategory d WHERE d.active = true AND ' . $bitwise . ' ORDER BY d.grouping' );
        $documents = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $formEmail = new \Project\Form\DocumentEmailForm( $this->getEntityManager() );

        $this->getView()
            ->setVariable( 'formEmail', $formEmail )
            ->setVariable( 'documents', $documents );

        return $this->getView();
    }

    private $architectural;

    private function isArchitectural()
    {
        if ( $this->architectural == null )
        {
            $dql = 'SELECT COUNT(p) FROM Space\Entity\System s JOIN s.space sp JOIN s.product p WHERE sp.project = :pid AND p.type=3';
            $q   = $this->getEntityManager()->createQuery( $dql );
            $q->setParameters( array( 'pid' => $this->getProject()->getProjectId() ) );
            $this->architectural = ($q->getSingleScalarResult() > 0);
        }

        return (bool)$this->architectural;
    }

    /**
     * action to get relevant form wizard config
     * @return \Zend\View\Model\JsonModel
     */
    public function wizardAction()
    {
        $categoryId = $this->params()->fromPost( 'documentId', false );
        if ( empty($categoryId) )
        {
            throw new \Exception( 'Illegal request' );
        }
        // grab document
        if ( ($this->getProject()->getType()->getTypeId() == 3) )
        { // TRIAL
            $bitwise = '(BIT_AND(d.compatibility, 64)=64)';
        }
        elseif ( ($this->getProject()->getStatus()->getJob() == 1) || (($this->getProject()->getStatus()->getWeighting() >= 1) && ($this->getProject()->getStatus()->getHalt() == 1)) )
        { // JOB
            $bitwise = '(BIT_AND(d.compatibility, 32)=32)';
        }
        else
        { // PROJECT
            $bitwise = '(BIT_AND(d.compatibility, 1)=1 ' . ($this->getProject()->hasState( 10 ) ? 'OR BIT_AND(d.compatibility, 4)=4' : '') . ')';
        }


        $query    = $this->getEntityManager()->createQuery( 'SELECT d.config FROM Project\Entity\DocumentCategory d WHERE d.active = true AND ' . $bitwise . ' AND d.documentCategoryId=' . $categoryId );
        $category = $query->getSingleResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
        if ( empty($category) )
        {
            throw new \Exception( 'document does not exist or is incompatible' );
        }

        $config = json_decode( $category['config'], true );
        if ( empty($config) )
        {
            $config = array();
        }

        if ( !empty($config['proposal']) )
        {
            if ( $this->getProject()->isFinanced() )
            {
                $config['attachments']['fmt'] = 1;
            }
        }
        $form = new \Project\Form\DocumentWizardForm( $this->getEntityManager(), $this->getProject(), $config );

        // set defaults
        foreach ( $config as $name => $value )
        {
            switch ( $name )
            {
                case 'user':
                    $form->get( 'user' )->setValue( $this->getProject()->getClient()->getUser()->getUserId() );
                    break;
                case 'billstyle':
                    // is this an architectural product
                    if ( $this->isArchitectural() )
                    {
                        $form->get( 'billstyle' )->setValue( 4 );
                    }
                    break;
                case 'proposal':
                    // is this an architectural product
                    if ( $this->isArchitectural() )
                    {
                        $form->get( 'billstyle' )->setValue( 4 );
                        $form->get( 'proposalstyle' )->setValue( 2 );
                    }

                    break;
            }
        }

        $contacts     = $this->getEntityManager()->getRepository( 'Contact\Entity\Contact' )->findBy( array(), array( 'forename' => 'asc' ) );
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal( true )
            ->setTemplate( 'project/projectitemdocument/wizard' )
            ->setVariables( array(
                'form'     => $form,
                'contacts' => $contacts,
                'project'  => $this->getProject()
            ) );

        $htmlOutput = $this->getServiceLocator()
            ->get( 'viewrenderer' )
            ->render( $htmlViewPart );

        $jsonModel = new JsonModel();
        $jsonModel->setVariables( array(
            'form' => $htmlOutput,
            'err'  => false,
        ) );

        return $jsonModel;
    }

    public function generateAction( array $argsX = array() )
    {
        // check for documentId param
        $categoryId = $this->params()->fromQuery( 'documentId', false );

        if ( empty($categoryId) )
        {
            throw new \Exception( 'Illegal request' );
        }

        $inline = !empty($this->params()->fromQuery( 'documentInline', false ));

        $data = $this->params()->fromQuery() + $argsX;

        $email = !empty($this->params()->fromQuery( 'email', false ));
        if ( $email )
        {
            try
            {
                $formEmail = new \Project\Form\DocumentEmailForm( $this->getEntityManager() );
                $formEmail->setInputFilter( new \Project\Filter\DocumentEmailFilter() );
                $formEmail->setData( $data );
                if ( !$formEmail->isValid() )
                {
                    return new JsonModel( array( 'err' => true, 'info' => $formEmail->getMessages() ) );
                }
            }
            catch ( \Exception $ex )
            {
                return new JsonModel( array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) ) );
            }
        }

        $em = $this->getEntityManager();
        // grab document
        if ( ($this->getProject()->getType()->getTypeId() == 3) )
        { // TRIAL
            $bitwise = '(BIT_AND(d.compatibility, 64)=64)';
        }
        elseif ( ($this->getProject()->getStatus()->getJob() == 1) || (($this->getProject()->getStatus()->getWeighting() >= 1) && ($this->getProject()->getStatus()->getHalt() == 1)) )
        { // JOB
            $bitwise = '(BIT_AND(d.compatibility, 32)=32)';
        }
        else
        { // PROJECT
            $bitwise = '(BIT_AND(d.compatibility, 1)=1 ' . ($this->getProject()->hasState( 10 ) ? 'OR BIT_AND(d.compatibility, 4)=4' : '') . ')';
        }


        $query    = $em->createQuery( 'SELECT d.documentCategoryId, d.location, d.name, d.description, d.config, d.partial FROM Project\Entity\DocumentCategory d WHERE d.active = true AND ' . $bitwise . ' AND d.documentCategoryId=' . $categoryId );
        $category = $query->getSingleResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
        if ( empty($category) )
        {
            throw new \Exception( 'document does not exist or is incompatible' );
        }


        $config  = empty($category['config']) ? array() : json_decode( $category['config'], true );
        $pdfVars = array(
            'resourcesUri' => getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR,
            'project'      => $this->getProject(),
            'footer'       => array(
                'pages' => true
            )
        );

        if ( !is_array( $config ) )
        {
            $config = array();
        }

        //{"con1":true,"usr1":true,"mdl1":false,"mdl2":true,"mdl3":false,"sur1":true,"model":true,"tac1":false,"autosave":true,"docsave":true,"quot":true,"adr1":true,"payterm":true}
        if ( !empty($config['proposal']) )
        {
            if ( $this->getProject()->isFinanced() )
            {
                $config['attachments']['fmt'] = 1;
                $pdfVars['financing']         = true;
            }
        }
        $form = new \Project\Form\DocumentWizardForm( $em, $this->getProject(), $config );
        $form->setInputFilter( new \Project\Filter\DocumentWizardInputFilter( $config ) );
        $form->setData( $data );

        if ( !$form->isValid() )
        {
            $this->debug()->dump( $form->getMessages() );
            throw new \Exception ( 'illegal configuration parameters' );
        }

        $autoSave = false;
        $formData = $form->getData();

        foreach ( $formData as $name => $value )
        {
            switch ( $name )
            {
                case 'contact':
                    $otherContact = $this->params()->fromQuery( 'otherContact' );
                    if ( $otherContact > 0 )
                    {
                        $value = $otherContact;
                    }

                    $pdfVars['contact'] = $em->find( 'Contact\Entity\Contact', $value );
                    break;
                case 'user':
                    $pdfVars['user'] = $em->find( 'Application\Entity\User', $value );
                    break;
                case 'dAddress':
                    /*
                    $otherContact = $this->params()->fromQuery( 'otherContact' );
                    if ( $otherContact > 0 )
                    {
                        $contact = $em->getRepository( 'Contact\Entity\Contact' )->find( $otherContact );
                        if ( $contact->getAddress() != Null )
                        {
                            $address_id          = $contact->getAddress()->getAddressId();
                            $pdfVars['dAddress'] = $em->find( 'Contact\Entity\Address', $address_id );
                        }
                        else
                        {
                            $pdfVars['dAddress'] = ''; //$em->find( 'Contact\Entity\Address', $value );
                        }
                    }*/
                    $otherAddress = $this->params()->fromQuery( 'otherAddress' );
                    if ( $otherAddress > 0 )
                    {
                        $pdfVars['dAddress'] = $em->find( 'Contact\Entity\Address', $otherAddress );
                    }
                    else
                    {
                        $pdfVars['dAddress'] = $em->find( 'Contact\Entity\Address', $value );
                    }

                    //$pdfVars['dAddress'] = $em->find( 'Contact\Entity\Address', $value );  // existing code

                    break;
                case 'dispatch':
                    $pdfVars['dispatch']      = $em->find( 'Job\Entity\Dispatch', $value );
                    $query                    = $em->createQuery( 'SELECT SUM(dp.quantity) AS quantity, p.model, p.description, p.productId '
                        . 'FROM Job\Entity\DispatchProduct dp '
                        . 'JOIN dp.product p '
                        . 'WHERE dp.dispatch = ' . $value . ' '
                        . 'GROUP BY p.productId'
                    );
                    $pdfVars['dispatchItems'] = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

                    $query                      = $em->createQuery( 'SELECT SUM(dp.quantity) AS quantity, p.productId '
                        . 'FROM Job\Entity\DispatchProduct dp '
                        . 'JOIN dp.dispatch d '
                        . 'JOIN dp.product p '
                        . 'WHERE dp.dispatch != ' . $value . ' AND d.revoked != true AND d.project = ' . $this->getProject()->getProjectId() . ' AND d.sent <= \'' . $pdfVars['dispatch']->getSent()->format( 'Y-m-d H:i:s' ) . '\' '
                        . 'GROUP BY p.productId'
                    );
                    $pdfVars['dispatchedItems'] = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

                    if ( empty($config['model']) )
                    {
                        $config['model'] = 4;
                    }
                    elseif ( ($config['model'] & 4) != 4 )
                    {
                        $config['model'] += 4;
                    }
                    break;
                case 'billstyle':
                    if ( !empty($formData['proposalstyle']) && ($formData['proposalstyle'] == 3) )
                    { // mears hack
                        $value = 5;
                    }

                    $pdfVars['billstyle'] = $value;
                    if ( ($config['model'] & 1) != 1 )
                    {
                        $config['model'] += 1;
                    }
                    if ( ($pdfVars['billstyle'] == 4) || ($pdfVars['billstyle'] == 5) )
                    { // important: in order to list the architectural elements individually as opposed to aggregated
                        if ( ($config['model'] & 2) != 2 )
                        {
                            $config['model'] += 2;
                        }
                    }
                    break;
                case 'modelyears':
                    $pdfVars['modelyears'] = $value;
                    break;
                /*case 'autosave':
                    $autoSave = (bool)$value;
                    break;/**/
                case 'AttachmentSections':
                    $pdfVars['attach'] = $value;
                    if ( is_array( $pdfVars['attach'] ) )
                    {
                        if ( in_array( 'arch', $pdfVars['attach'] ) )
                        {
                            if ( ($config['model'] & 2) != 2 )
                            {
                                $config['model'] += 2;
                            }
                        }

                        if ( in_array( 'spaces', $pdfVars['attach'] ) )
                        {
                            if ( ($config['model'] & 2) != 2 )
                            {
                                $config['model'] += 2;
                            }
                        }
                    }

                    break;
                default:
                    $pdfVars['form'][$name] = $value;
                    break;
            }
        }


        $pdfVars['form'] = $form->getData();


        if ( empty($config['size']) )
        {
            $config['size'] = 'pdf';
        }

        if ( empty($config['orientation']) )
        {
            $config['orientation'] = 'portrait';
        }

        $config['name'] = $category['name'] . ' ' . date( 'Y-m-d H:i:s' );

        //echo '<pre>', print_r($config, true), '</pre>'; die('<br />end');

        $pdf = new PdfModel();
        foreach ( $config as $option => $value )
        {
            switch ( $option )
            {
                case 'bcode':
                    break;
                case 'checkpoint':
                    $save                 = $this->saveConfig();
                    $pdfVars['invoiceNo'] = $save->getSaveId();
                    $config['name'] .= ' [' . (!empty($save->getName()) ? $save->getName() . ' | ' . $save->getSaveId() : $save->getSaveId()) . ']';
                    $notify = true;

                    if ( !empty($config['saveMode']) )
                    {
                        if ( ($config['saveMode'] & 4) == 4 )
                        { // conditional save type #1
                            $qb = $em->createQueryBuilder();
                            $qb
                                ->select( 'dl' )
                                ->from( '\Project\Entity\DocumentList', 'dl' )
                                ->where( 'dl.project = ' . $this->getProject()->getProjectId() )
                                ->andWhere( 'dl.user = ' . $this->getUser()->getUserId() )
                                ->andWhere( 'dl.category = ' . $categoryId )
                                //->andWhere('dl.created >= \''.date('Y-m-d H:i:s', time()-(60*5)).'\'')   // in last 5 mins
                                ->orderBy( 'dl.created', 'DESC' );

                            $query = $qb->getQuery();
                            $query->setMaxResults( 1 );
                            $items    = $query->getResult();
                            $autoSave = true;
                            if ( !empty($items) )
                            {
                                $item = array_shift( $items );
                                if ( !empty($item->getConfig()) )
                                {
                                    $itemConfig = json_decode( $item->getConfig() );
                                    if ( !empty($itemConfig->invoiceId) )
                                    {
                                        if ( $itemConfig->invoiceId == $save->getSaveId() )
                                        {
                                            $autoSave         = false;
                                            $config['notify'] = false;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ( !empty($config['notify']) )
                    {
                        if ( $save->getUpdated() )
                        {
                            // we need to notify accounts
                            $this->getGoogleService()->sendGmail(
                                'Document Created Notification: ' . strtoupper( $category['name'] ),
                                'A new ' . $category['name'] . ' has been created:<br /><br />'
                                . '<table>'
                                . '<tr><td>Date:</td><td>' . $save->getCreated()->format( 'l jS F, Y \a\t H:i' ) . '</td></tr>'
                                . '<tr><td>User:</td><td>' . ucwords( $this->getUser()->getName() ) . '</td></tr>'
                                . '<tr><td>Document:</td><td>' . ucwords( $category['name'] ) . '</td></tr>'
                                . '<tr><td>Client:</td><td><a href="http://projis.8p3.co.uk' . $this->url()->fromRoute( 'client', array( 'id' => $this->getProject()->getClient()->getClientId() ) ) . '">' . $this->getProject()->getClient()->getName() . '</a></td></tr>'
                                . '<tr><td>Project:</td><td><a href="http://projis.8p3.co.uk' . $this->url()->fromRoute( 'project', array( 'cid' => $this->getProject()->getClient()->getClientId(), 'pid' => $this->getProject()->getProjectId() ) ) . '">' . $this->getProject()->getName() . ' [' .
                                str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . ' / ' . $save->getSaveId() . ']</a></td></tr>'
                                . '<tr><td>Link:</td><td><a href="http://projis.8p3.co.uk' . $this->url()->fromRoute( 'project', array( 'cid' => $this->getProject()->getClient()->getClientId(), 'pid' => $this->getProject()->getProjectId() ) ) . '">' .
                                'http://projis.8p3.co.uk' . $this->url()->fromRoute( 'project', array( 'cid' => $this->getProject()->getClient()->getClientId(), 'pid' => $this->getProject()->getProjectId() ) ) . '</a></td></tr>'
                                . '</table><br /><br />Note: This email has been sent by the Projis auto-email system.  Please do not reply to this email as the account is an unmonitored account and email will be automatically deleted.',
                                array( 'quotes@8point3led.co.uk' ),
                                array( 'system' => true ) );/**/
                        }
                    }

                    break;
                case 'saveMode':
                    if ( empty($autoSave) )
                    { // if we have already set an autosave value
                        if ( ($value & 1) == 1 )
                        { // save on download
                            $autoSave = !$inline;
                        }
                        elseif ( ($value & 2) == 2 )
                        { // always save
                            $autoSave = true;
                        }
                    }

                    break;
                case 'name': // Triggers PDF download, automatically appends ".pdf" - this will not be inline
                    if ( $inline )
                        continue;
                    $pdf->setOption( 'filename', $value );
                    break;
                case 'orientation':
                    if ( $value == 'landscape' )
                    {
                        $pdf->setOption( 'paperOrientation', 'landscape' ); // Defaults to "portrait"
                    }
                    break;
                case 'size':
                    $pdf->setOption( 'paperSize', $value ); // Defaults to "8x11"
                    break;
                case 'model':
                    if ( ($value & 1) == 1 )
                    {
                        $years               = (!empty($pdfVars['modelyears']) ? (int)$pdfVars['modelyears'] : 12);
                        $service             = $this->getModelService()->payback( $this->getProject(), $years, (!empty($data['systemId']) ? array( 'systemId' => $data['systemId'] ) : array()) );
                        $pdfVars['figures']  = $service['figures'];
                        $pdfVars['forecast'] = $service['forecast'];
                        //$this->debug()->dump($service['figures'], false);
                        //$this->debug()->dump($service['forecast']);
                    }

                    if ( ($value & 2) == 2 )
                    {
                        $service              = $this->getModelService()->spaceBreakdown( $this->getProject(), (!empty($data['systemId']) ? array( 'systemId' => $data['systemId'] ) : array()) );
                        $pdfVars['breakdown'] = $service;
                    }

                    if ( ($value & 4) == 4 )
                    {
                        $billitems            = $this->getModelService()->billitems( $this->getProject() );
                        $pdfVars['billitems'] = $billitems;
                    }

                    if ( ($value & 8) == 8 )
                    {
                        $pdfVars['breakdown'] = $this->getModelService()->trialBreakdown( $this->getProject() );

                        $dql = 'SELECT SUM(sys.ppu * sys.quantity) '
                            . 'FROM Space\Entity\System sys '
                            . 'JOIN sys.space s '
                            . 'JOIN sys.product prd '
                            . 'JOIN s.project p '
                            . 'WHERE s.project = :pid AND prd.type=100';
                        $q   = $this->getEntityManager()->createQuery( $dql );
                        $q->setParameters( array( 'pid' => $this->getProject()->getProjectId() ) );
                        $pdfVars['installCost'] = $q->getSingleScalarResult();

                        $dql = 'SELECT SUM(sys.ppu * sys.quantity) '
                            . 'FROM Space\Entity\System sys '
                            . 'JOIN sys.space s '
                            . 'JOIN sys.product prd '
                            . 'JOIN s.project p '
                            . 'WHERE s.project = :pid AND prd.type=101';
                        $q   = $this->getEntityManager()->createQuery( $dql );
                        $q->setParameters( array( 'pid' => $this->getProject()->getProjectId() ) );
                        $pdfVars['deliveryCost'] = $q->getSingleScalarResult();

                    }

                    break;

                default:
                    $pdf->setOption( $option, (string)$value ); // Defaults to "8x11"
                    break;
            }
        }

        if ( !empty($config['bcode']) )
        {
            switch ( $config['bcode'] )
            {
                case 1:
                    if ( !empty($pdfVars['breakdown']) )
                    {
                        $dir              = $this->documentService->getSaveLocation( array( 'route' => array( 'barcodes', 'space' ) ) );
                        $pdfVars['dirbc'] = $dir;
                        foreach ( $pdfVars['breakdown'] as $buildingId => $building )
                        {
                            foreach ( $building['spaces'] as $spaceId => $space )
                            {
                                $file = $dir . $spaceId . '.jpg';
                                if ( file_exists( $file ) )
                                {
                                    continue;
                                }

                                $image = \Zend\Barcode\Barcode::draw(
                                    'code39',
                                    'image',
                                    array(
                                        'text'     => 'SP' . $spaceId,
                                        'drawText' => false,
                                        'font'     => 3
                                    ),
                                    array(
                                        'imageType' => 'jpg',
                                    )
                                ); /**/

                                imagejpeg( $image, $file, 100 );
                            }
                        }
                    }
                    break;
            }
        }

        $pdf->setVariables( $pdfVars );

        $pdf->setTemplate( 'project/projectitemdocument/' . $category['partial'] );

        $this->AuditPlugin()->auditProject( $inline ? 402 : 401, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId(), array(
            'documentCategory' => $category['documentCategoryId']
        ) );
        if ( $autoSave || $email )
        {
            $pdfOutput = $this->getServiceLocator()
                ->get( 'viewrenderer' )
                ->render( $pdf );

            $dompdf = new \DOMPDF();
            $dompdf->load_html( $pdfOutput );
            $dompdf->render();

            $route = array();
            if ( !empty($category['location']) )
            {
                $route = explode( '/', trim( $category['location'], '/' ) );
            }
            $invoiceNo = empty($pdfVars['invoiceNo']) ? false : $pdfVars['invoiceNo'];

            $this->documentService->setUser( $this->getUser() );
            $info = $this->documentService->saveDOMPdfDocument(
                $dompdf,
                array(
                    'filename' => $config['name'],
                    'route'    => $route,
                    'category' => $categoryId,
                ), $invoiceNo );/**/

            if ( $email )
            {
                $googleService = $this->getGoogleService();
                $googleService->setProject( $this->getProject() );

                if ( !$googleService->hasGoogle() )
                {
                    throw new \Exception ( 'account does not support emails' );
                }

                $response = $googleService->sendGmail( $formEmail->get( 'emailSubject' )->getValue(), $formEmail->get( 'emailMessage' )->getValue(), array( $formEmail->get( 'emailRecipient' )->getValue() ), array(
                    'attachment' => array( $info['file'] )
                ) );

                return new JsonModel( array( 'err' => false, 'info' => $response ) );
            }
            elseif ( $inline )
            {
                $dompdf->stream( 'filename', array( 'Attachment' => 0 ) );
            }
            else
            {
                $dompdf->stream( $pdf->getOption( 'filename', 'download' ) );
            }

            exit;
        }
        else
        {
            return $pdf;
        }

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
            ->setVariable( 'imageCategories', $imageCategories );

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
            ->setVariable( 'imageCategories', $imageCategories );

        return $this->getView();
    }


    private function scan( $dir, $base )
    {
        $files = array();

        // Is there actually such a folder/file?

        if ( file_exists( $base . $dir ) )
        {

            foreach ( scandir( $base . $dir ) as $f )
            {

                if ( !$f || $f[0] == '.' )
                {
                    continue; // Ignore hidden files
                }

                if ( is_dir( $base . $dir . DIRECTORY_SEPARATOR . $f ) )
                {

                    // The path is a folder

                    $files[] = array(
                        "name"  => $f,
                        "type"  => "folder",
                        "path"  => $dir . DIRECTORY_SEPARATOR . $f,
                        "items" => $this->scan( $dir . DIRECTORY_SEPARATOR . $f, $base ) // Recursively get the contents of the folder
                    );
                }

                else
                {

                    // It is a file

                    $files[] = array(
                        "name" => $f,
                        "type" => "file",
                        "path" => $dir . '/' . $f,
                        "size" => filesize( $base . $dir . DIRECTORY_SEPARATOR . $f ) // Gets the size of this file
                    );
                }
            }

        }

        return $files;
    }

    public function explorerScanAction()
    {
        try
        {
            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                //throw new \Exception('illegal request format');
            }

            $dir   = $this->documentService->getSaveLocation();
            $parts = explode( DIRECTORY_SEPARATOR, trim( $dir, DIRECTORY_SEPARATOR ) );
            $dir   = array_pop( $parts );
            $base  = DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR;


            // Run the recursive function

            $data = $this->scan( $dir, $base );

            $data = array(
                "name"  => $dir,
                "type"  => "folder",
                "path"  => $dir,
                "items" => $data
            );

            //$this->debug()->dump($data);


            // Output the directory listing as JSON

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array() : $data );/**/


    }

    public function listAction()
    {
        try
        {
            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                throw new \Exception( 'illegal request format' );
            }

            $categoryId = $this->params()->fromPost( 'category', false );

            if ( empty($categoryId) )
            {
                $categoryId = $this->params()->fromQuery( 'category', false );
                if ( empty($categoryId) )
                {
                    throw new \Exception( 'no request category found' );
                }
            }

            $category = $this->getEntityManager()->find( 'Project\Entity\DocumentCategory', $categoryId );
            if ( !($category instanceof \Project\Entity\DocumentCategory) )
            {
                throw new \Exception( 'illegal category' );
            }

            $config['route'] = array();
            if ( !empty($category->getLocation()) )
            {
                $config['route'] = explode( '/', trim( $category->getLocation(), '/' ) );
            }

            $subId = $this->params()->fromPost( 'subid', false );
            if ( empty($subId) )
            {
                $subId = $this->params()->fromQuery( 'subid', false );
            }

            $dropzone = $this->params()->fromQuery( 'dropzone', false );
            if ( empty($subId) )
            {
                $dropzone = $this->params()->fromPost( 'dropzone', false );
            }


            $dir = $this->documentService->getSaveLocation( $config );

            $docData = array();
            $docs    = $this->getEntityManager()->getRepository( 'Project\Entity\DocumentList' )->findByProjectId( $this->getProject()->getProjectId(), array( 'categoryId' => $categoryId, 'subid' => $subId ), true );

            if ( $dropzone )
            {
                foreach ( $docs as $doc )
                {
                    $data[] = array(
                        'name' => $doc['filename'],
                        'size' => $doc['size'],
                        'dlid' => $doc['documentListId']
                    );
                }
            }
            else
            {
                foreach ( $docs as $doc )
                {
                    $docData[] = array(
                        $doc['documentListId'],
                        $doc['filename'],
                        ($doc['size'] > (1024 * 1024)) ? (ceil( $doc['size'] / (1024 * 1024) )) . ' MB' : (($doc['size'] > 1024) ? (ceil( $doc['size'] / 1024 )) . ' KB' : $doc['size'] . ' B'),
                        $doc['extension'],
                        $doc['forename'] . ' ' . $doc['surname'],
                        $doc['created']->format( 'jS F \a\t H:i' ),
                        file_exists( $dir . (!empty($doc['subid']) ? $doc['subid'] . DIRECTORY_SEPARATOR : '') . $doc['filename'] ),
                    );
                }

                // create form
                $data = array( 'err' => false, 'data' => $docData );
            }

        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? ($dropzone ? array() : array( 'err' => true )) : $data );/**/
    }

    public function downloadAction()
    {
        try
        {
            $documentListId = $this->params()->fromQuery( 'documentListId', false );
            if ( empty($documentListId) )
            {
                throw new \Exception( 'no document id found' );
            }

            $document = $this->getEntityManager()->find( 'Project\Entity\DocumentList', $documentListId );

            if ( !($document instanceof \Project\Entity\DocumentList) )
            {
                throw new \Exception( 'document not found' );
            }

            $config['route'] = array();
            if ( !empty($document->getCategory()->getLocation()) )
            {
                $config['route'] = explode( '/', trim( $document->getCategory()->getLocation(), '/' ) );
            }

            if ( !empty($document->getSubId()) )
            {
                $config['route'][] = $document->getSubId();
            }

            $filename = $this->documentService->getSaveLocation( $config ) . $document->getFilename();
            if ( !file_exists( $filename ) )
            {
                throw new \Exception( 'file does not exist' );
            }

            $response = new \Zend\Http\Response\Stream();
            $response->setStream( fopen( $filename, 'r' ) );
            $response->setStatusCode( 200 );

            $headers = new \Zend\Http\Headers();
            $headers->addHeaderLine( 'Content-Type', $document->getExtension()->getHeader() )
                ->addHeaderLine( 'Content-Disposition', 'attachment; filename="' . $document->getFilename() . '"' )
                ->addHeaderLine( 'Content-Length', filesize( $filename ) );

            $response->setHeaders( $headers );

            return $response;
        }
        catch ( \Exception $ex )
        {
            exit; // just exit as file does not exist
        }
    }

    function downloadrawAction()
    {
        try
        {
            $route = $this->params()->fromQuery( 'route', false );
            if ( empty($route) )
            {
                throw new \Exception( 'no document id found' );
            }
            $route    = explode( DIRECTORY_SEPARATOR, trim( $route, DIRECTORY_SEPARATOR ) );
            $filename = array_pop( $route );

            $file = realpath( $this->documentService->getSaveLocation() . implode( DIRECTORY_SEPARATOR, $route ) . DIRECTORY_SEPARATOR . $filename );

            if ( !file_exists( $file ) )
            {
                throw new \Exception( 'File could not be found' );
            }

            $extension = $this->documentService->findExtensionFromExt( preg_replace( '/^[\s\S]+[.]([^.]+)$/', '$1', $filename ), 'application/octet-stream' );

            $response = new \Zend\Http\Response\Stream();
            $response->setStream( fopen( $file, 'r' ) );
            $response->setStatusCode( 200 );

            $headers = new \Zend\Http\Headers();
            $headers->addHeaderLine( 'Content-Type', $extension->getHeader() )
                ->addHeaderLine( 'Content-Disposition', 'attachment; filename="' . $filename . '"' )
                ->addHeaderLine( 'Content-Length', filesize( $file ) );

            $response->setHeaders( $headers );

            return $response;
        }
        catch ( \Exception $ex )
        {
            exit; // just exit as file does not exist
        }
    }

    function uploadrawAction()
    {
        //$this->AuditPlugin()->audit(3, $this->getUser()->getUserId());
        try
        {
            $file = $this->params()->fromFiles( 'file', false );
            if ( !empty($file) )
            {
                $route = $this->params()->fromPost( 'route', false );
                if ( empty($route) )
                {
                    throw new \Exception( 'No route found' );
                }

                $route   = explode( DIRECTORY_SEPARATOR, $route );
                $base    = array_shift( $route );
                $matches = array();
                if ( !preg_match( '/^[[]([\d]{5})[-]([\d]{5})/', $base, $matches ) )
                {
                    throw new \Exception( 'No route found' );
                }

                $clientId  = (int)$matches[1];
                $projectId = (int)$matches[2];

                if ( $this->getProject()->getProjectId() != $projectId )
                {
                    throw new \Exception( 'Project Mismatch' );
                }

                $data = $this->documentService->saveUploadedRawFile( $file, $route );

                return new JsonModel( array( 'err' => false, 'info' => $data ) );/**/

            }
            else
            {
                throw new \Exception( 'No files found' );
            }
        }
        catch ( \Exception $ex )
        {
            return new JsonModel( array( 'err' => true, 'info' => $ex->getMessage() ) );/**/
        }
    }

    function uploadAction()
    {
        try
        {
            $categoryId = $this->params()->fromQuery( 'category', false );

            if ( empty($categoryId) )
            {
                throw new \Exception( 'no request category found' );
            }

            $category = $this->getEntityManager()->find( 'Project\Entity\DocumentCategory', $categoryId );
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

            // Note: we use bitwise comparison on the compatibility field: (1=project, 2=job, 4=spare, 8=images, 16=generated)
            if ( ($category->getCompatibility() & 8) == 8 )
            { // this is an image
                // we need to check config to determine which additional query params are required
                if ( preg_match( '/spaces$/', $category->getLocation() ) )
                {
                    $spaceId = $this->params()->fromQuery( 'space', false );
                    if ( empty($spaceId) )
                    {
                        throw new \Exception( 'no space identifier found' );
                    }

                    $space = $this->getEntityManager()->find( 'Space\Entity\Space', $spaceId );
                    if ( !($space instanceof \Space\Entity\Space) )
                    {
                        throw new \Exception( 'illegal space' );
                    }

                    if ( $space->getProject()->getProjectId() != $this->getProject()->getProjectId() )
                    {
                        throw new \Exception( 'project mismatch' );
                    }

                    $config['subid']   = $space->getSpaceId();
                    $config['route'][] = $space->getSpaceId();
                }
            }

            $file = $this->params()->fromFiles( 'file', false );
            if ( !empty($file) )
            {
                $this->documentService->setUser( $this->getUser() );
                $this->documentService->saveUploadedFile( $file, $config );

            }
            else
            {
                throw new \Exception( 'No files found' );
            }


        }
        catch ( \Exception $ex )
        {
            die ($ex->getMessage());
        }

        die();
    }

    /**
     * export system model csv action
     * @return \Zend\Mvc\Controller\AbstractController
     */
    function exportProductListAction()
    {
        $em       = $this->getEntityManager();
        $discount = ($this->getProject()->getMcd());
        $query    = $em->createQuery( 'SELECT  p.mcd, p.productId, p.model, p.eca, p.attributes, pt.service, pt.name AS productType, pt.name as type, s.ppu, s.cpu, b.name as brand, '
            . 'SUM(s.quantity) AS quantity, '
            . 'SUM(s.ppu*s.quantity) AS price, '
            . 'SUM(ROUND((s.ppu * (1 - (' . $discount . ' * p.mcd))),2) * s.quantity) AS priceMCD, '
            . 'SUM(s.cpu*s.quantity) AS cost '
            . 'FROM Space\Entity\System s '
            . 'JOIN s.space sp '
            . 'JOIN s.product p '
            . 'JOIN p.brand b '
            . 'JOIN p.type pt '
            . 'WHERE sp.project=' . $this->getProject()->getProjectId() . ' '
            . 'GROUP BY s.product, s.ppu '
            . 'ORDER BY s.product' );
        $system   = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $data[] = array(
            '"Model"',
            '"Brand"',
            '"Sage Code"',
            '"CPU"',
            '"PPU"',
            '"PPU MCD"',
            '"Cost"',
            '"Price"',
            '"Quantity"',
            '"Type"',
            '"Philips Model"',
            '"Philips EOC"',
            '"Link"',
        );

        if ( !empty($system) )
        {
            $uri = $this->getRequest()->getUri();
            foreach ( $system as $item )
            {
                $attr   = json_decode( $item['attributes'], true );
                $data[] = array(
                    '"' . $item['model'] . '"',
                    '"' . $item['brand'] . '"',
                    $item['productId'],
                    $item['cpu'],
                    $item['ppu'],
                    $item['ppu'] * (1 - ($discount * $item['mcd'])),
                    $item['cost'],
                    $item['priceMCD'],
                    $item['quantity'],
                    '"' . $item['type'] . '"',
                    (!empty($attr['philips']['model']) ? '"' . $attr['philips']['model'] . '"' : ''),
                    (!empty($attr['philips']['eoc']) ? '"' . $attr['philips']['eoc'] . '"' : ''),
                    '"' . sprintf( '%s://%s', $uri->getScheme(), $uri->getHost() ) . '/product-' . $item['productId'] . '"',
                );
            }
        }/**/

        $filename = 'Product List - ' . str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . '.csv';

        $response = $this->prepareCSVResponse( $data, $filename );


        return $response;
    }

    /**
     * export system model csv action
     * @return \Zend\Mvc\Controller\AbstractController
     */
    function exportDealSheetAction()
    {
        $em       = $this->getEntityManager();
        $discount = ($this->getProject()->getMcd());
        $query    = $em->createQuery( 'SELECT  p.mcd, p.productId, p.model, p.eca, p.attributes, pt.service, pt.name AS productType, pt.name as type, s.ppu, s.cpu, b.name as brand, '
            . 'SUM(s.quantity) AS quantity, '
            . 'SUM(s.ppu*s.quantity) AS price, '
            . 'SUM(ROUND((s.ppu * (1 - (' . $discount . ' * p.mcd))),2) * s.quantity) AS priceMCD, '
            . 'SUM(s.cpu*s.quantity) AS cost '
            . 'FROM Space\Entity\System s '
            . 'JOIN s.space sp '
            . 'JOIN s.product p '
            . 'JOIN p.brand b '
            . 'JOIN p.type pt '
            . 'WHERE sp.project=' . $this->getProject()->getProjectId() . ' '
            . 'GROUP BY s.product, s.ppu '
            . 'ORDER BY s.product' );
        $system   = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $data[] = array(
            'Client:',
            $this->getProject()->getClient()->getName(),
        );

        $data[] = array(
            'Job:',
            $this->getProject()->getName() . '(' . $this->getProject()->getProjectId() . ')',
        );

        $data[] = array(
            'Reference:',
            str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ),
        );
        $data[] = array();

        $data[]              = array(
            '"Model"',
            '"Brand"',
            '"Type"',
            '"Sage Code"',
            '"CPU"',
            '"PPU"',
            '"Price"',
            '"PPU MCD"',
            '"Cost"',
            '"Price MCD"',
            '"Quantity"',
            '"Margin Val"',
            '"Margin %"'
        );
        $total_price         = 0;
        $total_cost          = 0;
        $total_priceMCD      = 0;
        $total_qty           = 0;
        $total_marginValue   = 0;
        $total_marginPercent = 0;
        if ( !empty($system) )
        {
            $uri = $this->getRequest()->getUri();
            foreach ( $system as $item )
            {
                $attr   = json_decode( $item['attributes'], true );
                $data[] = array(
                    '"' . $item['model'] . '"',
                    '"' . $item['brand'] . '"',
                    '"' . $item['type'] . '"',
                    $item['productId'],
                    number_format( $item['cpu'], 2, '.', '' ),
                    number_format( $item['ppu'], 2, '.', '' ),
                    number_format( $item['price'], 2, '.', '' ),
                    number_format( ($item['ppu'] * (1 - ($discount * $item['mcd']))), 2, '.', '' ),
                    number_format( $item['cost'], 2, '.', '' ),
                    number_format( $item['priceMCD'], 2, '.', '' ),
                    $item['quantity'],
                    number_format( ($item['priceMCD'] - $item['cost']), 2, '.', '' ),
                    number_format( round( (($item['priceMCD'] - $item['cost']) / $item['priceMCD']) * 100, 2 ), 2, '.', '' ) . ' %'
                );

                $total_price += $item['price'];
                $total_cost += $item['cost'];
                $total_priceMCD += $item['priceMCD'];
                $total_qty += $item['quantity'];
                $total_marginValue += ($item['priceMCD'] - $item['cost']);
            }

            $total_marginPercent = ($total_marginValue / $total_priceMCD) * 100;

            $data[] = array();

            // Adding totals
            $data[]         = array(
                '',
                '',
                '',
                '',
                '',
                'Full Price',
                number_format( $total_price, 2, '.', '' ),
                '',
                number_format( $total_cost, 2, '.', '' ),
                number_format( $total_priceMCD, 2, '.', '' ),
                $total_qty,
                number_format( $total_marginValue, 2, '.', '' ),
                number_format( $total_marginPercent, 2, '.', '' ) . ' %'
            );
            $discount_value = $total_price - $total_priceMCD;
            // Adding totals
            $data[] = array(
                '',
                '',
                '',
                '',
                '',
                'Discount Amt',
                number_format( $discount_value, 2, '.', '' ),
                '',
                '',
                '',
                '',
                '',
                ''
            );

            $data[] = array(
                '',
                '',
                '',
                '',
                '',
                'Discount %',
                number_format( (($discount_value / $total_price) * 100), 2, '.', '' ) . ' %',
                '',
                '',
                '',
                '',
                '',
                ''
            );

        }/**/


        $filename = 'Deal Sheet - ' . str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . '.csv';

        $response = $this->prepareCSVResponse( $data, $filename );


        return $response;
    }

    /**
     * export system model csv action
     * @return \Zend\Mvc\Controller\AbstractController
     */
    function exportSystemAction()
    {
        $data[] = array(
            '"Building ID"',
            '"Building Name"',
            '"Space ID"',
            '"Space Name"',
            '"Label"',
            '"Legacy Lighting"',
            '"Legacy Quantity"',
            '"Weekly Hours of Operation"',
            '"Specified Length"',
            '"Achievable Length"',
            '"Life Span"',
            '"Legacy Rating"',
            '"LED Replacement"',
            '"LED Quantity"',
            '"LED Rating"',
            '"Power Saving"',
            '"kW Saving"',
            '"Electricity Savings Achievable Per Annum"',
            '"CO2 Reductions Achievable Per Annum"',
            '"Total Price"',
            '"Discounted Price"',
            '"Configuration"',
        );

        $years   = $this->params()->fromQuery( 'modelyears', $this->getProject()->getModel() );
        $spaceId = $this->params()->fromQuery( 'spaceId', 0 );
        $args    = array();
        if ( !empty($spaceId) )
        {
            $args['spaceId'] = $spaceId;
        }

        $service  = $this->getModelService()->payback( $this->getProject(), $years, $args );
        $forecast = $service['forecast'];
        $figures  = $service['figures'];

        $breakdown = $this->getModelService()->spaceBreakdown( $this->getProject(), $args );
        $financing = !empty($figures['finance_amount']);


        foreach ( $breakdown as $buildingId => $building )
        {
            foreach ( $building['spaces'] as $spaceId => $space )
            {
                foreach ( $space['products'] as $systemId => $system )
                {
                    $attributes = json_decode( $system[16] );
                    $arch       = ($system[2] == 3);
                    $cStr       = '';
                    if ( $arch )
                    {
                        foreach ( $attributes->dConf as $aConfigs )
                        {
                            if ( !empty($cStr) )
                            {
                                $cStr .= ' | ';
                            }
                            foreach ( $aConfigs as $aConfig => $aQty )
                            {
                                for ( $i = 0; $i < $aQty; $i++ )
                                {
                                    $cStr .= '[' . $aConfig . ']';
                                }
                            }

                        }
                    }
                    $led = ($system[2] == 1);
                    $row = array(
                        $buildingId,
                        '"' . $building['name'] . '"',
                        $spaceId,
                        '"' . $space['name'] . '"',
                        '"' . (empty($system[17]) ? '-' : $system[17]) . '"', // label
                        '"' . $system[18] . '"', // legacy light name
                        $system[9], // hours of operation
                        $system[6], // legacy quantity
                        $arch ? $attributes->sLen : '0', // specified length
                        $arch ? $attributes->dLen : '0', // achievable length
                        $led ? ($system[9] ? number_format( 50000 / ($system[9] * 52), 2 ) : 0) : 0, // life span
                        $system[10], // legacy rating
                        $system[4], // LED model
                        $system[5], // Quantity
                        $system[7], // LED rating
                        $system[9], // power saving
                        $system[15], // kW saving
                        $system[13], // Elec saving
                        $system[14], // CO2 reductions
                        $system[0], // Total Price
                        $system[1], // Total Price (inc discount)
                        $arch ? '"' . $cStr . '"' : '',
                    );

                    $data[] = $row;
                }
            }


        }


        $cells = array(
            array( 'Year' ),
            array( 'Cumulative Carbon Savings' ),
            array( 'Carbon Allowance' ),
            array( 'Current Spend' ),
            array( 'LED Spend' ),
            array( 'Electricity Savings' ),
            array( 'Maintenance Savings' ),
            array( 'LED Maintenance Costs' ),
            array( 'Monthly Cost (No LED)' ),
            array( 'Net Cash Saving' ),
            array( 'Cumulative Savings' ),
            array( 'Payback' ),
            array( 'Payback with ECA' ),
        );

        for ( $i = 1; $i <= $years; $i++ )
        {
            $cells[0][]  = $i;
            $cells[1][]  = $forecast[$i][5];
            $cells[2][]  = $forecast[$i][10];
            $cells[3][]  = $forecast[$i][0];
            $cells[4][]  = $forecast[$i][1];
            $cells[5][]  = $forecast[$i][2];
            $cells[6][]  = $forecast[$i][3];
            $cells[7][]  = $forecast[$i][13];
            $cells[8][]  = $forecast[$i][6];
            $cells[9][]  = $forecast[$i][4];
            $cells[10][] = $forecast[$i][5];
            $cells[11][] = $forecast[$i][8];
            $cells[12][] = $forecast[$i][9];
        }

        $data[] = array();
        $data[] = array();

        foreach ( $cells as $cell )
        {
            $data[] = $cell;
        }

        $data[] = array();
        $data[] = array();

        $data[] = array( 'Projected ECA Eligibility', $figures['eca'] );
        $data[] = array( 'Total ' . $years . ' Year Carbon Saving', $figures['carbon'] );
        $data[] = array( 'Total ' . $years . ' Year Saving', $figures['saving'] );
        if ( $financing )
        {
            $data[] = array( 'Average Cash Benefit Over Funding Period', $figures['finance_avg_benefit'] );
            $data[] = array( 'Average Repayments Over Funding Period', $figures['finance_avg_repay'] );
            $data[] = array( 'Average Net Annual Benefit Over Funding Period', $figures['finance_avg_netbenefit'] );
            $data[] = array( 'Net Cash Benefit Over Funding Period', $figures['finance_netbenefit'] );
        }
        $data[] = array( 'LED Cost', $figures['cost_led'] );
        $data[] = array( 'Installation Cost', $figures['cost_install'] );

        if ( $figures['cost_delivery'] > 0 )
        {
            $data[] = array( 'Delivery Cost', $figures['cost_delivery'] );
        }
        if ( $figures['cost_ibp'] > 0 )
        {
            $data[] = array( 'Insurance Backed Premium Cost', $figures['cost_ibp'] );
        }
        if ( $figures['cost_access'] > 0 )
        {
            $data[] = array( 'Access Cost', $figures['cost_access'] );
        }
        if ( $figures['cost_prelim'] > 0 )
        {
            $data[] = array( 'Prelim Fee', $figures['cost_prelim'] );
        }
        if ( $figures['cost_overheads'] > 0 )
        {
            $data[] = array( 'Overheads Fee', $figures['cost_overheads'] );
        }
        if ( $figures['cost_management'] > 0 )
        {
            $data[] = array( 'Management Fee', $figures['cost_management'] );
        }

        $data[] = array( 'Total Cost', $figures['cost'] );
        $data[] = array( 'Total Cost Less ECA', $figures['costeca'] );
        $data[] = array( 'VAT at 20%', ($figures['costvat'] - $figures['cost']) );
        $data[] = array( 'Total Cost (incl VAT)', $figures['costvat'] );
        $data[] = array( 'Total Cost (incl VAT) Less ECA', $figures['costvateca'] );
        $data[] = array( 'Total ' . $years . ' Year Profit', $figures['profit'] );
        $data[] = array( 'Total ' . $years . ' Year Profit with ECA', $figures['profiteca'] );

        /*$this->debug()->dump($data, false);
        $this->debug()->dump($breakdown, false);
        $this->debug()->dump($service);/**/

        $filename = 'Full System Model - ' . str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . '.csv';

        $response = $this->prepareCSVResponse( $data, $filename );

        return $response;
    }


    /**
     * export system model csv action
     * @return \Zend\Mvc\Controller\AbstractController
     */
    function exportSystemBuildAction()
    {
        $data[] = array(
            '"Building ID"',
            '"Building Name"',
            '"Space ID"',
            '"Space Name"',
            '"Label"',
            '"Legacy Lighting"',
            '"Legacy Quantity"',
            '"Specified Length"',
            '"Achievable Length"',
            '"Weekly Hours of Operation"',
            '"Life Span"',
            '"Legacy Rating"',
            '"Product"',
            '"Product Type"',
            '"LED Quantity"',
            '"LED Rating"',
            '"Configuration"',
            '"A"',
            '"B"',
            '"B1"',
            '"C"',
            '"End Caps"',
            '"Red/Black Cable"',
            '"WAGO"',
            '"Phosphor"',
            '"Aluminium"',
        );

        $args = array();

        $modelService = $this->getModelService();
        $breakdown    = $modelService->spaceBreakdown( $this->getProject(), $args );


        foreach ( $breakdown as $buildingId => $building )
        {
            foreach ( $building['spaces'] as $spaceId => $space )
            {
                foreach ( $space['products'] as $systemId => $system )
                {
                    $attributes = json_decode( $system[16], true );
                    $arch       = ($system[2] == 3);
                    $cStr       = '';
                    if ( $arch )
                    {
                        $boards = array(
                            '_A'  => array( $system[3], 'A Board', 'PCB Boards Type A', 0 ),
                            '_B'  => array( $system[3], 'B Board', 'PCB Boards Type B', 0 ),
                            '_B1' => array( $system[3], 'B1 Board', 'PCB Boards Type B1', 0 ),
                            '_C'  => array( $system[3], 'C Board', 'PCB Boards Type C', 0 ),
                        );

                        $architectural = array(
                            '_EC'  => array( false, 'End Caps', 'Board group end caps', 0 ),
                            '_ECT' => array( false, 'End Caps (Terminating)', 'Board group terminating end caps', 0 ),
                            '_CBL' => array( false, '200mm Cable', '200mm black and red cable', 0 ),
                            '_WG'  => array( false, 'Wago Connectors', 'Wago Connectors', 0 ),
                        );

                        $phosphor  = array();
                        $aluminium = array();

                        $modelService->getPickListItems( $attributes, $boards, $architectural, $phosphor, $aluminium );
                        foreach ( $attributes['dConf'] as $aConfigs )
                        {
                            if ( !empty($cStr) )
                            {
                                $cStr .= ' | ';
                            }
                            foreach ( $aConfigs as $aConfig => $aQty )
                            {
                                for ( $i = 0; $i < $aQty; $i++ )
                                {
                                    $cStr .= '[' . $aConfig . ']';
                                }
                            }

                        }

                        $phosphorStr = array();
                        foreach ( $phosphor as $len => $config )
                        {
                            foreach ( $config as $brds => $qtty )
                            {
                                $q             = $qtty[0] + $qtty[1];
                                $phosphorStr[] = "{$len} x {$q}";
                            }
                        }

                        $aluminiumStr = array();
                        foreach ( $aluminium as $len => $config )
                        {
                            foreach ( $config as $brds => $qtty )
                            {
                                $aluminiumStr[] = "{$len} x {$qtty}";
                            }
                        }

                    }
                    $led = ($system[2] == 1);
                    $row = array(
                        $buildingId,
                        '"' . $building['name'] . '"',
                        $spaceId,
                        '"' . $space['name'] . '"',
                        '"' . (empty($system[17]) ? '-' : $system[17]) . '"', // label
                        '"' . $system[18] . '"', // legacy light name
                        $system[6], // legacy quantity
                        $arch ? $attributes['sLen'] : '0', // specified length
                        $arch ? $attributes['dLen'] : '0', // achievable length
                        $system[9], // hours of operation
                        $led ? ($system[9] ? number_format( 50000 / ($system[9] * 52), 2 ) : 0) : 0, // life span
                        $system[10], // legacy rating
                        $system[4], // LED model
                        ($system[2] == 3) ? 'Architectural' : (($system[2] == 2) ? 'Control' : (($system[2] == 1) ? 'LED' : 'Product')), // LED model
                        $system[5], // Quantity
                        $system[7], // LED rating
                        $arch ? '"' . $cStr . '"' : '',
                        $arch ? $boards['_A'][3] : 0,
                        $arch ? $boards['_B'][3] : 0,
                        $arch ? $boards['_B1'][3] : 0,
                        $arch ? $boards['_C'][3] : 0,
                        $arch ? $architectural['_EC'][3] : 0,
                        $arch ? $architectural['_CBL'][3] : 0,
                        $arch ? $architectural['_WG'][3] : 0,
                        $arch ? implode( ' | ', $phosphorStr ) : 0,
                        $arch ? implode( ' | ', $aluminiumStr ) : 0,
                    );

                    $data[] = $row;
                }
            }


        }


        /*$this->debug()->dump($data);
        $this->debug()->dump($breakdown, false);
        $this->debug()->dump($service);/**/

        $filename = 'System Build - ' . str_pad( $this->getProject()->getClient()->getClientId(), 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $this->getProject()->getProjectId(), 5, "0", STR_PAD_LEFT ) . ' ' . date( 'dmyHis' ) . '.csv';

        $response = $this->prepareCSVResponse( $data, $filename );

        return $response;
    }


    public function deliveryNoteGenerateAction()
    {
        try
        {
            $em = $this->getEntityManager();

            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                throw new \Exception( 'illegal request format' );
            }

            $post = $this->params()->fromPost();

            $systemConf = array();
            $breakdown  = $this->getModelService()->billitems( $this->getProject(), array( 'products' => true ) );
            foreach ( $breakdown as $item )
            {
                $systemConf[$item['productId']] = $item['quantity'];
            }

            if ( empty($post['productId']) )
            {
                throw new \Exception( 'No items added to delivery note' );
            }

            if ( count( $post['productId'] ) != count( $post['quantity'] ) )
            {
                throw new \Exception( 'Post item count mismatch' );
            }

            $conf = array();
            foreach ( $post['productId'] as $id => $val )
            {
                if ( empty($post['quantity'][$id]) )
                    continue;
                $conf[$val] = $post['quantity'][$id];
            }

            if ( empty($conf) )
            {
                throw new \Exception( 'No quantities added to delivery note' );
            }

            $query    = $em->createQuery( 'SELECT SUM(dp.quantity) AS Quantity, p.productId '
                . 'FROM Job\Entity\DispatchProduct dp '
                . 'JOIN dp.dispatch d '
                . 'JOIN dp.product p '
                . 'WHERE d.project = ' . $this->getProject()->getProjectId() . ' '
                . 'GROUP BY p.productId'
            );
            $existing = $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

            foreach ( $existing as $prodQuantity )
            {
                if ( !empty($conf[$prodQuantity['productId']]) )
                {
                    if ( ($prodQuantity['Quantity'] + $conf[$prodQuantity['productId']]) > $systemConf[$prodQuantity['productId']] )
                    {
                        throw new \Exception( 'Delivery note quantities for some products exceed project product totals' );
                    }
                }
            }

            $form = new \Job\Form\DeliveryNoteForm( $em, $this->getProject() );
            $form->setInputFilter( new \Job\Filter\DeliveryNoteFilter() );

            $form->setData( $post );
            if ( $form->isValid() )
            {
                $dispatch     = new \Job\Entity\Dispatch();
                $post['sent'] = \DateTime::createFromFormat( 'd/m/Y', $post['sent'] );
                $hydrator     = new DoctrineHydrator( $em, 'Job\Entity\Dispatch' );
                $hydrator->hydrate(
                    $post,
                    $dispatch
                );
                $dispatch->setUser( $this->getUser() );
                $dispatch->setProject( $this->getProject() );

                $em->persist( $dispatch );

                $dispatchItems = array();
                $hydrator      = new DoctrineHydrator( $em, 'Job\Entity\DispatchProduct' );
                foreach ( $conf as $productId => $quantity )
                {
                    $dispatchProduct = new \Job\Entity\DispatchProduct();
                    $hydrator->hydrate(
                        array(
                            'quantity' => $quantity,
                            'product'  => $productId,
                        ),
                        $dispatchProduct
                    );
                    $dispatchProduct->setDispatch( $dispatch );
                    $em->persist( $dispatchProduct );
                    $dispatchItems[] = array(
                        'model'       => $dispatchProduct->getProduct()->getModel(),
                        'description' => $dispatchProduct->getProduct()->getDescription(),
                        'quantity'    => $dispatchProduct->getQuantity(),
                    );
                }

                $em->flush();

                // now create and save delivery note
                $config  = array( 'name' => 'Delivery Note #' . str_pad( $dispatch->getDispatchId(), 5, "0", STR_PAD_LEFT ) );
                $pdfVars = array(
                    'resourcesUri' => getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR,
                    'project'      => $this->getProject(),
                    'footer'       => array(
                        'pages' => true
                    )
                );

                $pdfVars['dispatch']      = $dispatch;
                $pdfVars['dispatchItems'] = $dispatchItems;;


                $pdf = new PdfModel();
                $pdf->setOption( 'paperSize', 'pdf' );
                $pdf->setOption( 'filename', $config['name'] );


                $pdf->setVariables( $pdfVars );
                $pdf->setTemplate( 'project/projectitemdocument/deliverynote/product' );


                $pdfOutput = $this->getServiceLocator()->get( 'viewrenderer' )->render( $pdf );
                $dompdf    = new \DOMPDF();
                $dompdf->load_html( $pdfOutput );
                $dompdf->render();

                $route = array( 'delivery note' );
                $this->documentService->setUser( $this->getUser() );

                $info = $this->documentService->saveDOMPdfDocument(
                    $dompdf,
                    array(
                        'filename' => $config['name'],
                        'route'    => $route,
                        'category' => 81, // delivery note
                    ) );


                $data = array( 'err' => false, 'info' => $info );
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
     * Get addresses based on contact id
     *
     * @return JsonModel
     */
    public function addressAction()
    {
        try
        {
            $em = $this->getEntityManager();
            if ( !$this->getRequest()->isXmlHttpRequest() )
            {
                throw new \Exception( 'Illegal request format' );
            }

            $contact_id = $this->params()->fromPost( 'contact_id' );

            $contact = $em->getRepository( 'Contact\Entity\Contact' )->find( $contact_id );
            if ( $contact->getAddress() != NULL )
            {
                $address_id = $contact->getAddress()->getAddressId();
                $results    = $em->find( 'Contact\Entity\Address', $address_id );
                $hydrate    = new DoctrineHydrator( $em, 'Contact\Entity\Address' );
                $addresses  = $hydrate->extract( $results );
            }
            else
            {
                $addresses = '';
            }

            return new JsonModel( array(
                'addresses' => $addresses
            ) );
        }
        catch ( \Exception $ex )
        {
            $data = array( 'err' => true, 'info' => array( 'ex' => $ex->getMessage() ) );
        }

        return new JsonModel( empty($data) ? array( 'err' => true ) : $data );/**/
    }
}