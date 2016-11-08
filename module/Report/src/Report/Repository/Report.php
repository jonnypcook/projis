<?php
namespace Report\Repository;

use Doctrine\ORM\EntityRepository;
use Report\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Report extends EntityRepository
{
    /**
     * Get chart data through ajax call
     */
    public function getOrderBookChartData()
    {
        $year = date( 'Y' );
        $month = date('m');
        if ( $month < 4 )
        {
            $year--;
        }

        for ( $i = $year; $i >= $year - 3; $i-- )
        {
            $from_date = mktime( 0, 0, 0, 4, 1, $i );
            $to_date   = mktime( 23, 59, 59, 3, 31, $i + 1 );

            $sql
                = 'SELECT 
                      MONTH(p.contracted) mth, 
                      YEAR(p.contracted) yr,
                      SUM(Sy.ppu * Sy.quantity * Sp.quantity) price
                    FROM Project p
                        JOIN `Client` c
                          ON c.`client_id` = p.`client_id`
                        JOIN `Space` Sp
                          ON Sp.project_id = p.project_id
                        JOIN `System`	Sy
                          ON Sy.space_id = Sp.space_id                  
                    WHERE
                     p.test = 0 AND
                     p.exclude_from_reporting = 0 AND
                      p.contracted IS NOT NULL AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\')
                    GROUP BY yr, mth
                    ORDER BY yr, mth';

            $stmt = $this->_em->getConnection()->prepare( $sql );
            $stmt->execute();
            $result = $stmt->fetchAll();

            $data[$i] = Array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

            if ( !empty($result) )
            {
                foreach ( $result as $r )
                {

                    if ( $r['mth'] == 4 )
                    {
                        $data[$i][0] = $r['price'];
                    }
                    if ( $r['mth'] == 5 )
                    {
                        $data[$i][1] = $r['price'];
                    }
                    if ( $r['mth'] == 6 )
                    {
                        $data[$i][2] = $r['price'];
                    }
                    if ( $r['mth'] == 7 )
                    {
                        $data[$i][3] = $r['price'];
                    }
                    if ( $r['mth'] == 8 )
                    {
                        $data[$i][4] = $r['price'];
                    }
                    if ( $r['mth'] == 9 )
                    {
                        $data[$i][5] = $r['price'];
                    }
                    if ( $r['mth'] == 10 )
                    {
                        $data[$i][6] = $r['price'];
                    }
                    if ( $r['mth'] == 11 )
                    {
                        $data[$i][7] = $r['price'];
                    }
                    if ( $r['mth'] == 12 )
                    {
                        $data[$i][8] = $r['price'];
                    }
                    if ( $r['mth'] == 1 )
                    {
                        $data[$i][9] = $r['price'];
                    }
                    if ( $r['mth'] == 2 )
                    {
                        $data[$i][10] = $r['price'];
                    }
                    if ( $r['mth'] == 3 )
                    {
                        $data[$i][11] = $r['price'];
                    }

                }
            }
        }

        $i = 0;
        foreach ( $data as $key => $value )
        {

            $data[$i]['name'] = '"' . $key . '-' . ($key + 1) . '"';
            $data[$i]['data'] = json_encode( $value );
            $i++;

        }


        return $data;
    }

    /**
     * Get chart data through ajax call
     */
    public function getOrderBookQuarterChartData()
    {
        $year = date( 'Y' );
        for ( $i = $year; $i >= $year - 3; $i-- )
        {
            $from_date = mktime( 0, 0, 0, 4, 1, $i );
            $to_date   = mktime( 23, 59, 59, 3, 31, $i + 1 );

            $sql
                = 'SELECT
                      MONTH(p.contracted) mth,
                      YEAR(p.contracted) yr,
                      SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                      QUARTER(p.contracted) qtr
                    FROM Project p
                        JOIN `Client` c
                          ON c.`client_id` = p.`client_id`
                        JOIN `Space` Sp
                          ON Sp.project_id = p.project_id
                        JOIN `System`	Sy
                          ON Sy.space_id = Sp.space_id
                    WHERE
                      p.contracted IS NOT NULL AND
                      p.test = 0 AND
                      p.exclude_from_reporting = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\')
                    GROUP BY yr, qtr
                    ORDER BY yr, mth';

            $stmt = $this->_em->getConnection()->prepare( $sql );
            $stmt->execute();
            $result = $stmt->fetchAll();

            $data[$i] = Array( 0, 0, 0, 0);

            if ( !empty($result) )
            {
                foreach ( $result as $r )
                {

                    if ( $r['qtr'] == 2 )
                    {
                        $data[$i][0] = $r['price'];
                    }
                    if ( $r['qtr'] == 3 )
                    {
                        $data[$i][1] = $r['price'];
                    }
                    if ( $r['qtr'] == 4 )
                    {
                        $data[$i][2] = $r['price'];
                    }
                    if ( $r['qtr'] == 1 )
                    {
                        $data[$i][3] = $r['price'];
                    }
                }
            }
        }

        $i = 0;
        foreach ( $data as $key => $value )
        {

            $data[$i]['name'] = '"' . $key . '-' . ($key + 1) . '"';
            $data[$i]['data'] = json_encode( $value );
            $i++;

        }

        return $data;
    }

    public function getOrderBookListing( $year, $month = '' )
    {
        if ( $month )
        {
            $sql
                = 'SELECT
                      p.project_id,
                      p.name          project_name,
                      c.name          client_name,
                      CONCAT(u.forename, " ", u.surname) user_name,
                      d.name as department_name,
                      c.client_id,
                      u.user_id,
                      SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                      SUM(Sy.cpu*Sy.quantity * Sp.quantity) AS cost,
                      SUM(ROUND((Sy.ppu * (1 - (pr.mcd * p.mcd))),2) * Sy.quantity * Sp.quantity) AS priceMCD,
                      p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
                    FROM `Project` p
                      JOIN `Client` c
                        ON c.`client_id` = p.`client_id`
                      JOIN `Space` Sp
                        ON Sp.project_id = p.project_id
                      JOIN `System`	Sy
                        ON Sy.space_id = Sp.space_id
                      Join `Product` pr
                        ON Sy.product_id = pr.product_id
                      JOIN `User` u
                        ON u.`user_id` = c.`user_id`
                      RIGHT JOIN `Department` d
                        ON d.`department_id` = u.`department_id`
                    WHERE
                      p.contracted IS NOT NULL AND
                      p.`test` = 0 AND
                      p.`exclude_from_reporting` = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      MONTH(p.contracted) = \'' . $month . '\' AND
                      YEAR(p.contracted) = \'' . $year . '\'
                    GROUP BY p.project_id
                    ORDER BY department_name, user_name, p.contracted';
        }
        else
        {
            $startYear = $year;
            $endYear = $year + 1;
            if ( in_array(date('m'), array(1,2,3)) )
            {
                $startYear  = $year - 1;
                $endYear = $year;
            }

            $from_date = mktime( 0, 0, 0, 4, 1, $startYear );
            $to_date   = mktime( 23, 59, 59, 3, 31, $endYear );
            $sql = 'SELECT
                      p.project_id,
                      p.name          project_name,
                      c.name          client_name,
                      CONCAT(u.forename, " ", u.surname) user_name,
                      u.user_id,
                      c.client_id,
                      d.name as department_name,
                      SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                      SUM(Sy.cpu*Sy.quantity * Sp.quantity) AS cost,
                      SUM(ROUND((Sy.ppu * (1 - (pr.mcd * p.mcd))),2) * Sy.quantity * Sp.quantity) AS priceMCD,
                      p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
                    FROM `Project` p
                     JOIN `Client` c
                        ON c.`client_id` = p.`client_id`
                     JOIN `Space` Sp
                        ON Sp.project_id = p.project_id
                     JOIN `System`	Sy
                        ON Sy.space_id = Sp.space_id
                     JOIN `Product` pr
                        ON Sy.product_id = pr.product_id
                    JOIN `User` u
                      ON u.`user_id` = c.`user_id`
                    RIGHT JOIN `Department` d
                        ON d.`department_id` = u.`department_id`
                    WHERE
                      p.contracted IS NOT NULL AND
                      p.`test` = 0 AND
                      p.`exclude_from_reporting` = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\')
                    GROUP BY p.project_id
                    ORDER BY department_name, user_name, p.contracted';
            }


        $stmt = $this->_em->getConnection()->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchAll(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        return $result;

    }

    public function getOrderBookQuarterListing( $year, $quarter = '' )
    {
        if ( $quarter )
        {
            $sql
                = 'SELECT
                      p.project_id,
                      p.name          project_name,
                      c.name          client_name,
                      CONCAT(u.forename, " ", u.surname) user_name,
                      u.user_id,
                      c.client_id,
                      d.name as department_name,
                      SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                      SUM(Sy.cpu*Sy.quantity * Sp.quantity) AS cost,
                      SUM(ROUND((Sy.ppu * (1 - (pr.mcd * p.mcd))),2) * Sy.quantity * Sp.quantity) AS priceMCD,
                      p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed,
                      QUARTER(p.contracted) qtr
                    FROM `Project` p
                      JOIN `Client` c
                        ON c.`client_id` = p.`client_id`
                      JOIN `Space` Sp
                        ON Sp.project_id = p.project_id
                      JOIN `System`	Sy
                        ON Sy.space_id = Sp.space_id
                      Join `Product` pr
                        ON Sy.product_id = pr.product_id
                      JOIN `User` u
                        ON u.`user_id` = c.`user_id`
                      JOIN `Department` d
                        ON d.`department_id` = u.`department_id`
                    WHERE
                      p.contracted is not null AND
                      p.`test` = 0 AND
                      p.`exclude_from_reporting` = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      QUARTER(p.contracted) = \'' . $quarter . '\' AND
                      YEAR(p.contracted) = \'' . $year . '\' AND
                      p.test = 0 AND
                      p.exclude_from_reporting = 0
                    GROUP BY p.project_id
                    ORDER BY department_name, user_name, p.contracted ASC';
        }
        else
        {
            $startYear = $year;
            $endYear = $year + 1;
            if ( in_array(date('m'), array(1,2,3)) )
            {
                $startYear  = $year - 1;
                $endYear = $year;
            }

            $from_date = mktime( 0, 0, 0, 4, 1, $startYear );
            $to_date   = mktime( 23, 59, 59, 3, 31, $endYear );
            $sql = 'SELECT
                      p.project_id,
                      p.name          project_name,
                      c.name          client_name,
                      CONCAT(u.forename, " ", u.surname) user_name,
                      u.user_id,
                      c.client_id,
                      d.name as department_name,
                      SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                      SUM(Sy.cpu * Sy.quantity * Sp.quantity) AS cost,
                      SUM(ROUND((Sy.ppu * (1 - (pr.mcd * p.mcd))),2) * Sy.quantity * Sp.quantity) AS priceMCD,
                      p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed,
                      QUARTER(p.contracted) qtr
                    FROM `Project` p
                      JOIN `Client` c
                        ON c.`client_id` = p.`client_id`
                      JOIN `Space` Sp
                        ON Sp.project_id = p.project_id
                      JOIN `System`	Sy
                        ON Sy.space_id = Sp.space_id
                      Join `Product` pr
                        ON Sy.product_id = pr.product_id
                      JOIN `User` u
                        ON u.`user_id` = c.`user_id`
                      JOIN `Department` d
                        ON d.`department_id` = u.`department_id`
                    WHERE
                      p.contracted is not null AND
                      p.`test` = 0 AND
                      p.`exclude_from_reporting` = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\') AND
                      p.test = 0 AND
                      p.exclude_from_reporting = 0
                    GROUP BY p.project_id
                    ORDER BY department_name, user_name, p.contracted ASC';
        }

        $stmt = $this->_em->getConnection()->prepare( $sql );
        $stmt->execute();
        $result = $stmt->fetchAll(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        return $result;

    }
}