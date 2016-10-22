<?php
namespace Application\Repository;
 
use Doctrine\ORM\EntityRepository;
use Application\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class State extends EntityRepository
{
    /**
     * find user by email address - used primarily for oAuth2
     * @param type $email
     * @param array $params
     * @return Application\Entity\User
     */
    public function findByCompatibility($compatibility, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('s')
            ->from('Application\Entity\State', 's')
            ->orderBy('s.name', 'ASC');
        
        if (is_int($compatibility)) {
            $queryBuilder->andWhere('BIT_AND(s.compatibility, '.$compatibility.')='.$compatibility);
        } elseif(is_array($compatibility)) {
            $compatibilityStr = array();
            foreach ($compatibility as $comp) {
                $compatibilityStr[] = '(BIT_AND(s.compatibility, '.$comp.')='.$comp.')';
            }
            $queryBuilder->andWhere(implode(' OR ', $compatibilityStr));
        }
        
        $query  = $queryBuilder->getQuery();
        
        return $query->getResult();

    }
    
    
}

