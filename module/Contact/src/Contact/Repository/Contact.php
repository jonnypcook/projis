<?php

namespace Contact\Repository;

use Doctrine\ORM\EntityRepository;
use Contact\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class Contact extends EntityRepository
{
    public function findByClientId( $client_id, $array = false, array $params = array() )
    {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery( "SELECT c "
            . "FROM Contact\Entity\Contact c "
            . "JOIN c.client cl "
            . "WHERE cl.clientId=" . $client_id . " "
            . "ORDER BY c.forename ASC, c.surname ASC" );

        if ( $array === true )
        {
            return $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
        }

        return $query->getResult();
    }


    public function findByProjectId( $project_id, $array = false, array $params = array() )
    {
        // First get the EM handle
        // and call the query builder on it
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select( 'c' )
            ->from( 'Contact\Entity\Contact', 'c' )
            ->innerJoin( "c.projects", "p", "WITH", "p=:projectid" )
            ->setParameter( "projectid", $project_id );

        $query = $queryBuilder->getQuery();

        if ( $array === true )
        {
            return $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
        }

        return $query->getResult();
    }

    public function findPaginateByCompanyId( $company_id, $length = 10, $start = 1, array $params = array() )
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select( 'c' )
            ->from( 'Contact\Entity\Contact', 'c' )
            ->innerJoin( 'c.client', 'cl' )
            ->innerJoin( 'cl.user', 'u' )
            ->leftJoin( 'c.address', 'a' )
            ->where( 'u.company = ' . $company_id )
            ->orderBy( 'c.forename', 'ASC' )
            ->orderBy( 'c.surname', 'ASC' );

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
                $queryBuilder->andWhere( 'cl.clientId = :cid' )
                    ->setParameter( 'cid', $keyword );
            }
            else
            {
                $queryBuilder->andWhere( 'c.forename LIKE :keyword OR c.surname LIKE :keyword OR CONCAT(c.forename, \' \', c.surname) LIKE :keyword2' )
                    ->setParameter( 'keyword', '%' . $keyword . '%' )
                    ->setParameter( 'keyword2', '%' . str_replace( ' ', '%', $keyword ) . '%' );
            }
        }    /**/

        /*
         * Ordering
         */
        if ( !empty($params['orderBy']) )
        {
            foreach ( $params['orderBy'] as $name => $dir )
            {
                switch ( $name )
                {
                    case 'title':
                        $queryBuilder->add( 'orderBy', 'c.title ' . $dir );
                        break;
                    case 'forename':
                        $queryBuilder->add( 'orderBy', 'c.forename ' . $dir );
                        break;
                    case 'surname':
                        $queryBuilder->add( 'orderBy', 'c.surname ' . $dir );
                        break;
                    case 'position':
                        $queryBuilder->add( 'orderBy', 'c.position ' . $dir );
                        break;
                    case 'telephone':
                        $queryBuilder->add( 'orderBy', 'c.telephone1 ' . $dir );
                        break;
                    case 'email':
                        $queryBuilder->add( 'orderBy', 'c.email ' . $dir );
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

    public function findByCompanyId( $company_id, $array = false, array $params = array() )
    {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery( "SELECT c "
            . "FROM Contact\Entity\Contact c "
            . "JOIN c.client cl "
            . "JOIN cl.user u "
            . "WHERE u.company=" . $company_id . " "
            . "ORDER BY c.forename ASC, c.surname ASC" );

        if ( $array === true )
        {
            return $query->getResult( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
        }

        return $query->getResult();
    }

    /**
     * Find contacts by quarter
     *
     * @param $quarter
     * @param $year
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByQuarter( $quarter, $year )
    {
        $sql
            = 'SELECT
                    c.contact_id, cl.client_id, c.forename, c.surname, cl.name client_name,
                     CONCAT(u.forename, " ", u.surname) user_name,
                     c.created
                FROM
                    `Contact` c
                INNER JOIN
                    `Client` cl
                    ON cl.`client_id` = c.`client_id`
                JOIN `User` u
                   ON u.`user_id` = cl.`user_id`
                WHERE
                    QUARTER(c.`created`) = ' . $quarter . '
                    AND Year(c.`created`) = ' . $year . '
                group by c.contact_id, cl.client_id
                ORDER BY c.created DESC';

        $stmt = $this->_em->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

