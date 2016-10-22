<?php

namespace Client\Repository;

use Doctrine\ORM\EntityRepository;
use Client\Entity;

class Client extends EntityRepository
{
    public function findByName( $name )
    {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery( "SELECT c FROM Client\Entity\Client c WHERE c.name='{$name}'" );

        return $query->getResult();
    }

    /**
     * Find clients by quarter
     *
     * @param $quarter
     * @param $year
     *
     * @return array
     */
    public function findByQuarter( $quarter, $year )
    {

        $sql = 'SELECT c.*, CONCAT(`u`.`forename`, " ", `u`.`surname`) user_name
            FROM `Client` `c`
            JOIN `User` `u`
              ON `u`.`user_id` = `c`.`user_id`
            WHERE
              QUARTER(c.created) = \''. $quarter .'\' AND
              YEAR(c.created)   = \''. $year .'\'
            ORDER by c.created DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

