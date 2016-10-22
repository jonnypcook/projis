<?php
namespace Application\Repository;
 
use Doctrine\ORM\EntityRepository;
use Application\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Property extends EntityRepository
{
    public function findByGrouping($grouping, $array=false, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from('Application\Entity\Property', 'p');
        
        if (is_array($grouping)) {
            foreach ($grouping as $group) {
                $queryBuilder->orWhere('BIT_AND(p.grouping, '.$group.')='.$group);
            }
        } else {
            $queryBuilder->where('BIT_AND(p.grouping, '.$grouping.')='.$grouping);
        }
        
        $query  = $queryBuilder->getQuery();
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();

    }
    

}

