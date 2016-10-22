<?php

namespace Resource\Repository;
 
use Doctrine\ORM\EntityRepository;
use Project\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Resource extends EntityRepository
{
    public function findResources()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('r')
            ->from('Resource\Entity\Resource', 'r')
            ->where('r.name != :name1')
            ->andWhere('r.name != :name2')
            ->setParameter('name1', 'Product')
            ->setParameter('name2', 'Invoice')
            ->orderBy('r.name', 'ASC');
        $query = $qb->getQuery();

        return $query->getResult();
    }
}

