<?php
namespace Application\Repository;
 
use Doctrine\ORM\EntityRepository;
use Application\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class ActivityType extends EntityRepository
{
    public function findByCompatibility($compatibility, $array=false, $params=array()) {
        
        $query = $this->getEntityManager()->createQuery('SELECT d.documentCategoryId, d.name, d.description, d.config, d.partial FROM Project\Entity\DocumentCategory d WHERE d.active = true AND BIT_AND(d.compatibility, 1)=1');
        
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('a')
            ->from('Application\Entity\ActivityType', 'a')
            ->where('BIT_AND(a.compatibility, '.$compatibility.')='.$compatibility)
            ->orderBy('a.name', 'ASC')
            
                ;
        
        $query  = $queryBuilder->getQuery();
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();

    }
        
}

