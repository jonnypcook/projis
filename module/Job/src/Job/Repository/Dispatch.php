<?php

namespace Job\Repository;

use Doctrine\ORM\EntityRepository;
use Project\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Dispatch extends EntityRepository
{
    public function findByProjectId( $project_id )
    {
        // First get the EM handle
        // and call the query builder on it
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select( 'd' )
            ->from( 'Job\Entity\Dispatch', 'd' )
            ->where( 'd.project = :projectId' )
            ->setParameter( "projectId", $project_id )
            ->add( 'orderBy', 'd.dispatchId ASC' );

        //$qb->setParameter(1, $service);
        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findPaginateByProjectId( $project_id, $length = 10, $start = 1, array $params = array() )
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select( 'd' )
            ->from( 'Job\Entity\Dispatch', 'd' )
            ->leftJoin( 'd.address', 'a' )
            ->innerJoin( 'd.user', 'u' )
            ->where( 'd.project = :projectId' )
            ->andWhere( 'd.revoked = false' )
            ->setParameter( "projectId", $project_id );


        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        if ( !empty($params['keyword']) )
        {
            $keyword = $params['keyword'];
            if ( preg_match( '/^[\d]+$/', trim( $keyword ) ) )
            {
                $keyword = (int)$keyword;
                $queryBuilder->andWhere( 'd.dispatchId LIKE :did' )
                    ->setParameter( 'did', '%' . $keyword . '%' );
            }
            else
            {
                $queryBuilder->andWhere( 'd.reference LIKE :reference' )
                    ->setParameter( 'reference', '%' . trim( preg_replace( '/[*]+/', '%', $keyword ), '%' ) . '%' );
            }
        }

        /*
         * Ordering
         */
        if ( !empty($params['orderBy']) )
        {
            foreach ( $params['orderBy'] as $name => $dir )
            {
                switch ( $name )
                {
                    case 'id':
                        $queryBuilder->add( 'orderBy', 'd.dispatchId ' . $dir );
                        break;
                    case 'created':
                        $queryBuilder->add( 'orderBy', 'd.created ' . $dir );
                        break;
                    case 'sent':
                        $queryBuilder->add( 'orderBy', 'd.sent ' . $dir );
                        break;
                    case 'postcode':
                        $queryBuilder->add( 'orderBy', 'a.postcode ' . $dir );
                        break;
                    case 'reference':
                        $queryBuilder->add( 'orderBy', 'd.reference ' . $dir );
                        break;
                    case 'owner':
                        $queryBuilder->add( 'orderBy', 'u.forename ' . $dir );
                        $queryBuilder->add( 'orderBy', 'u.surname ' . $dir );
                        break;
                }
            }
        }

        /**/
        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator( new ORMPaginator( $queryBuilder ) )
        );

        $start = (floor( $start / $length ) + 1);


        $paginator
            ->setCurrentPageNumber( $start )
            ->setItemCountPerPage( $length );

        return $paginator;

    }


}

