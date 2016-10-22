<?php

namespace Product\Repository;
 
use Doctrine\ORM\EntityRepository;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Product extends EntityRepository
{
    public function findByType($typeId, $params=array()) {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery("SELECT ".
                "p "
                . "FROM Product\Entity\Product p "
                . "WHERE p.type = {$typeId}");
        
        if (!empty($params['array'])) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    }
    
    
    public function findByProjectId($projectId, $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('DISTINCT p')
            ->from('Product\Entity\Product', 'p')
            ->innerJoin('p.systems', 's')
            ->innerJoin('s.space', 'sp')
            ->where('sp.project = :projectId')
            ->andWhere('p.type = 1')
            ->setParameter("projectId", $projectId);
        
        $query = $queryBuilder->getQuery();
        
        return $query->getResult();
    }
    
    
    public function findBySpaceId($spaceId, $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('DISTINCT p')
            ->from('Product\Entity\Product', 'p')
            ->innerJoin('p.systems', 's')
            ->innerJoin('s.space', 'sp')
            ->where('sp.spaceId = :spaceId')
            ->andWhere('p.type = 1')
            ->setParameter("spaceId", $spaceId);
        
        $query = $queryBuilder->getQuery();
        
        return $query->getResult();
    }
    
    
}

