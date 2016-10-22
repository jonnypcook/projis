<?php
namespace Job\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Entity\User;
use Application\Controller\AuthController;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;


class JobController extends AuthController
{

    public function indexAction()
    {
        $this->setCaption( 'Active Jobs' );

        return new ViewModel( array() );
    }

    public function listAction()
    {
        $em = $this->getServiceLocator()->get( 'Doctrine\ORM\EntityManager' );

        if ( !$this->request->isXmlHttpRequest() )
        {
            throw new \Exception( 'illegal request type' );
        }

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select( 'p' )
            ->from( 'Project\Entity\Project', 'p' )
            ->innerJoin( 'p.client', 'c' )
            ->innerJoin( 'p.status', 'ps' )
            ->innerJoin( 'p.type', 'pt' )
            ->innerJoin( 'c.user', 'u' )
            ->where( 'pt.typeId != 3' )// do we allow trials in here?
            ->andWhere( 'ps.job = true' );

        $viewMode = $this->params()->fromQuery( 'fViewMode', 1 );


        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        $keyword = $this->params()->fromQuery( 'sSearch', '' );
        $keyword = trim( $keyword );
        if ( !empty($keyword) )
        {
            if ( preg_match( '/^[\d]+$/', trim( $keyword ) ) )
            {
                $keyword = (int)$keyword;
                $queryBuilder->andWhere( 'p.projectId LIKE :pid' )
                    ->setParameter( 'pid', '%' . $keyword . '%' );
            }
            else
            {
                $queryBuilder->andWhere( 'p.name LIKE :name' )
                    ->setParameter( 'name', '%' . trim( preg_replace( '/[*]+/', '%', $keyword ), '%' ) . '%' );
            }
        }
        else
        { // if we keyword search then ignore filter setting
            $checkViewMode = true;
            if ( !$this->isGranted( 'admin.all' ) )
            {
                if ( !$this->isGranted( 'project.share' ) )
                {
                    $checkViewMode = false;
                    $queryBuilder->leftJoin( "p.collaborators", "col", "WITH", "col=:userId" );
                    $queryBuilder->andWhere( 'u.userId = :userId OR col.userId = :userId' )
                        ->setParameter( 'userId', $this->getUser()->getUserId() );
                }
            }

            if ( $checkViewMode )
            {
                switch ( $viewMode )
                {
                    case 1:
                        $queryBuilder->leftJoin( "p.collaborators", "col", "WITH", "col=:userId" );
                        $queryBuilder->andWhere( 'u.userId = :userId OR col.userId = :userId' )
                            ->setParameter( 'userId', $this->getUser()->getUserId() );
                        break;
                    case 2:
                        $queryBuilder->andWhere( 'u.userId = :userId' )
                            ->setParameter( 'userId', $this->getUser()->getUserId() );
                        break;
                    case 3:
                        $queryBuilder->leftJoin( "p.collaborators", "col", "WITH", "col=:userId" );
                        $queryBuilder->andWhere( 'u.company = :companyId OR col.userId = :userId' )
                            ->setParameter( 'companyId', $this->getUser()->getCompany()->getCompanyId() )
                            ->setParameter( 'userId', $this->getUser()->getUserId() );
                        break;
                }
            }
        }


        /*
         * Ordering
         */
        $aColumns = array( 'p.name', 'c.name', 'u.forename', 'p.created', 'p.projectId' );
        $orderByP = $this->params()->fromQuery( 'iSortCol_0', false );
        $orderBy  = array();
        if ( $orderByP !== false )
        {
            for ( $i = 0; $i < intval( $this->params()->fromQuery( 'iSortingCols', 0 ) ); $i++ )
            {
                $j = $this->params()->fromQuery( 'iSortCol_' . $i );

                if ( $this->params()->fromQuery( 'bSortable_' . $j, false ) == "true" )
                {
                    $dir = $this->params()->fromQuery( 'sSortDir_' . $i, 'ASC' );
                    if ( is_array( $aColumns[$j] ) )
                    {
                        foreach ( $aColumns[$j] as $ac )
                        {
                            $orderBy[] = $ac . " " . $dir;
                        }
                    }
                    else
                    {
                        $orderBy[] = $aColumns[$j] . " " . ($dir);
                    }
                }
            }

        }
        if ( empty($orderBy) )
        {
            $orderBy[] = 'p.name ASC';
        }

        foreach ( $orderBy as $ob )
        {
            $queryBuilder->add( 'orderBy', $ob );
        }

        /**/

        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator( new ORMPaginator( $queryBuilder ) )
        );

        $length = $this->params()->fromQuery( 'iDisplayLength', 10 );
        $start  = $this->params()->fromQuery( 'iDisplayStart', 1 );
        $start  = (floor( $start / $length ) + 1);


        $paginator
            ->setCurrentPageNumber( $start )
            ->setItemCountPerPage( $length );

        $data = array(
            "sEcho"                => intval( $this->params()->fromQuery( 'sEcho', false ) ),
            "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
            "iTotalRecords"        => $paginator->getcurrentItemCount(),
            "aaData"               => array()
        );/**/


        foreach ( $paginator as $page )
        {
            //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
            $data['aaData'][] = array(
                '<a href="javascript:" class="action-project-edit"  pid="' . $page->getprojectId() . '" cid="' . $page->getClient()->getClientId() . '">' . $page->getName() . '</a>',
                '<a href="javascript:" class="action-client-edit"  cid="' . $page->getClient()->getClientId() . '">' . $page->getClient()->getName() . '</a>',
                $page->getClient()->getUser()->getHandle(),
                $page->getCreated()->format( 'd/m/Y H:i' ),
                str_pad( $page->getProjectId(), 5, "0", STR_PAD_LEFT ),
                '<button class="btn btn-primary action-client-edit" pid="' . $page->getProjectId() . '" cid="' . $page->getClient()->getClientId() . '" ><i class="icon-pencil"></i></button>',
            );
        }

        return new JsonModel( $data );/**/
    }

}