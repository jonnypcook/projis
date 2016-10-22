<?php

namespace Resource\Repository;
 
use Doctrine\ORM\EntityRepository;
use Project\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class CostCode extends EntityRepository
{
    public function findCostCodes()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('cc')
            ->from('Resource\Entity\CostCode', 'cc')
            ->where('cc.name != :name1')
            ->andWhere('cc.name != :name2')
            ->setParameter('name1', 'Product')
            ->setParameter('name2', 'Invoice')
            ->orderBy('cc.name');

        $query = $qb->getQuery();

        return $query->getResult();
    }
}

