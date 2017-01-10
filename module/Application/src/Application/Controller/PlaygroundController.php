<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\View\Model\JsonModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;



class PlaygroundController extends AuthController
{
    public function indexAction()
    {
        $this->setCaption('Playground');
        
        /*$this->getView()
                ->setVariable('count', count($projects))
                ->setVariable('term', $term)
                ->setVariable('projects', $projects);/**/

        return $this->getView();
    }
    
    public function routemappingAction() {
        $this->setCaption('Google Route Mapping Demo');
        
        /*$this->getView()
                ->setVariable('count', count($projects))
                ->setVariable('term', $term)
                ->setVariable('projects', $projects);/**/

        return $this->getView();
    }
    
    public function colourdimmingAction() {
        $this->setCaption('Colour Dimming Demo');
        
        return $this->getView();
    }

    public function hmrcAction() {
        $this->setCaption('HM Prisons Demo');

        return $this->getView();
    }

    /**
     * liteIP action
     * @return mixed
     */
    public function liteipLocatorAction() {
        $this->setCaption('LitIP LED locator');
        $liteIpService = $this->getLiteIpService();

        $qbs  = $this->getEntityManager()->createQueryBuilder();
        $qbs->select('DISTINCT pr.ProjectID')
            ->from('Application\Entity\LiteipDrawing', 'd')
            ->innerJoin('d.project', 'pr')
            ->where('d.Activated = true');

        $qb  = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
            ->from('Application\Entity\LiteipProject', 'p')
            ->where($qb->expr()->in('p.ProjectID', $qbs->getDQL()))
            ->add('orderBy', 'p.ProjectDescription ASC');

        $query  = $qb->getQuery();
        $projects = $query->getResult();

        return $this->getView()->setVariable('projects', $projects);
    }


    /**
     * get liteip drawing image action
     * @return \Zend\Http\Response\Stream
     */
    public function liteipdrawingimageAction() {
        try
        {
            $drawingId = $this->params()->fromQuery( 'drawingId', false );

            $filename = $this->getLiteIpService()->findDrawingUrl($drawingId);

            if ($filename === false) {
                throw new \Exception('Could not find drawing');
            }

            if ( !file_exists( $filename ) )
            {
                throw new \Exception( 'file does not exist: ' . $filename);
            }

            $imageType = strtolower(preg_replace( '/^[\s\S]+[.]([^.]+)$/', '$1', $filename ));

            $response = new \Zend\Http\Response\Stream();
            $response->setStream( fopen( $filename, 'r' ) );
            $response->setStatusCode( 200 );

            $headers = new \Zend\Http\Headers();
            $headers->addHeaderLine( 'Content-Type', "image/{$imageType}" )
                ->addHeaderLine( 'Content-Length', filesize( $filename ) );

            $response->setHeaders( $headers );

            return $response;
        }
        catch ( \Exception $ex )
        {
            echo $ex->getMessage();
            exit; // just exit as file does not exist
        }
    }

    /**
     * list drawings for project
     * @return JsonModel
     * @throws \Exception
     */
    public function liteipdrawinglistAction() {
        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }

        $projectID = $this->params()->fromPost('projectId', false);

        if ($projectID === false || !preg_match('/^[\d]+$/', $projectID)) {
            return new JsonModel(array('error' => false, 'drawings' => array()));
        }

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('d.DrawingID, d.Drawing, d.Width, d.Height')
            ->from('Application\Entity\LiteipDrawing', 'd')
            ->where('d.project=?1')
            ->add('orderBy', 'd.Drawing ASC')
            ->setParameter(1, $projectID);
        $drawings = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return new JsonModel(array('error' => false, 'drawings' => $drawings));
    }

    /**
     * list devices for liteip drawing
     * @return JsonModel
     * @throws \Exception
     */
    public function liteipdevicelistAction() {
        $em = $this->getEntityManager();

        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }

        $drawingId = $this->params()->fromQuery('fDrawingId', 1);
        $plotMode = $this->params()->fromQuery('plot', false);
        $queryBuilder = $em->createQueryBuilder();

        if ($plotMode) {
            $queryBuilder
                ->select('d')
                ->from('Application\Entity\LiteipDevice', 'd')
                ->where('d.drawing = :did')
                ->setParameter('did', $drawingId);

            $deviceData = array();
            $devices = $queryBuilder->getQuery()->getResult();

            foreach($devices as $device) {
                $deviceData[] = array(
                    $device->getDeviceID(),
                    $device->getDeviceSN(),
                    $device->IsIsE3(),
                    !empty($device->getStatus()) ? $device->getStatus()->isFault() : false,
                    !empty($device->getStatus()) ? $device->getStatus()->getName() : 'n\a',
                    !empty($device->getLastE3StatusDate()) ? $device->getLastE3StatusDate()->format('Y-m-d H:i:s') : '',
                    $device->getPosLeft(),
                    $device->getPosTop()
                );
            }

            return new JsonModel(array('error' => false, 'devices' => $deviceData));
        }

        $queryBuilder
            ->select('d')
            ->from('Application\Entity\LiteipDevice', 'd')
            ->where('d.drawing = :did')
            ->setParameter('did', $drawingId);



        /*
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        $devices = trim($this->params()->fromQuery('sSearch',''));
        if (!empty($devices)) {
            $devices = explode(',', preg_replace('/[,]+/', ',', str_replace(' ', ',',trim($devices))));
            $queryBuilder->andWhere($queryBuilder->expr()->in('d.DeviceID', '?1'))
                ->setParameter(1, $devices);
        }


        /*
         * Ordering
         */
        $aColumns = array('d.DeviceID','d.IsE3','d.status');
        $orderByP = $this->params()->fromQuery('iSortCol_0',false);
        $orderBy = array();
        if ($orderByP!==false)
        {
            for ( $i=0 ; $i<intval($this->params()->fromQuery('iSortingCols',0)) ; $i++ )
            {
                $j = $this->params()->fromQuery('iSortCol_'.$i);

                if ( $this->params()->fromQuery('bSortable_'.$j, false) == "true" )
                {
                    $dir = $this->params()->fromQuery('sSortDir_'.$i,'ASC');
                    if (is_array($aColumns[$j])) {
                        foreach ($aColumns[$j] as $ac) {
                            $orderBy[] = $ac." ".$dir;
                        }
                    } else {
                        $orderBy[] = $aColumns[$j]." ".($dir);
                    }
                }
            }

        }

        if (empty($orderBy)) {
            $orderBy[] = 'd.DeviceID ASC';
        }

        foreach ($orderBy as $ob) {
            $queryBuilder->add('orderBy', $ob);
        }

        /**/

        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($queryBuilder))
        );

        $length = $this->params()->fromQuery('iDisplayLength', 10);
        $start = $this->params()->fromQuery('iDisplayStart', 1);
        $start = (floor($start / $length)+1);


        $paginator
            ->setCurrentPageNumber($start)
            ->setItemCountPerPage($length);

        $data = array(
            "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
            "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
            "iTotalRecords" => $paginator->getcurrentItemCount(),
            "aaData" => array()
        );/**/


        foreach ($paginator as $page) {
            //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
            $data['aaData'][] = array (
                '<a class="serial-trigger" data-device-serial="' . $page->getDeviceSN() . '">' . $page->getDeviceSN() . '</a>',
                $page->isIsE3() ? 'Yes' : 'No',
                empty($page->getStatus()) ? 'n\a' : $page->getStatus()->getName(),
                $page->getDrawing()->getDrawing(),
                $page->getDrawing()->getProject()->getProjectDescription()
            );
        }

        return new JsonModel($data);/**/
    }

    public function liteIpRefreshDevicesAction() {
        if (!$this->request->isXmlHttpRequest()) {
            throw new \Exception('illegal request type');
        }

        $drawingId = $this->params()->fromQuery('fDrawingId', false);

        if ($drawingId !== false) {
            $this->getLiteIpService()->synchronizeDevicesData($drawingId);
        }

        return new JsonModel(array('error' => false));
    }

    /**
     * get LiteIp Service
     * @return \Application\Service\LiteIpService
     */
    public function getLiteIpService() {
        return $this->getServiceLocator()->get('LiteIpService');
    }



}
