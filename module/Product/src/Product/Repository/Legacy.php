<?php

namespace Product\Repository;
 
use Doctrine\ORM\EntityRepository;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Legacy extends EntityRepository
{
    public function findByLegacyId($legacyId, $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
                
            ->select(empty($params['array'])?'l':
                    'l.description, l.quantity, l.pwr_item, l.pwr_ballast, l.emergency, l.dim_item, l.dim_unit, l.legacyId, '
                    . 'c.categoryId, '
                    . 'p.productId')
            ->from('Product\Entity\Legacy', 'l')
            ->innerJoin('l.category', 'c')
            ->innerJoin('l.product', 'p')
            ->where('l.legacyId = :legacyId')
            ->setParameter("legacyId", $legacyId);
        
        $query = $queryBuilder->getQuery();
        
        if (!empty($params['array'])) {
            return $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getSingleResult();
    }
    
    
}

