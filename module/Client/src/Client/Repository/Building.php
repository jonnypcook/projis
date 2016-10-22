<?php

namespace Client\Repository;
 
use Doctrine\ORM\EntityRepository;
use Client\Entity;
 
class Building extends EntityRepository
{
    public function findByClientId($client_id) {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery("SELECT b FROM Client\Entity\Building b JOIN b.client cl WHERE cl.clientId=".$client_id." ORDER BY b.name ASC");
        return $query->getResult();
    }

    public function findByProjectId($project_id) {
        // First get the EM handle
        // and call the query builder on it
        $qb  = $this->getEntityManager()->createQueryBuilder();
        $qb2 = $qb;
        $qb2->select('b2.buildingId')
            ->from('Space\Entity\Space', 's')
            ->innerJoin('s.building', 'b2')
            ->where('s.project = '.$project_id)
            ->andWhere('s.deleted != true');
        
        $qb  = $this->getEntityManager()->createQueryBuilder();
        $qb->select('b')
            ->from('Client\Entity\Building', 'b')
            ->where($qb->expr()->in('b.buildingId', $qb2->getDQL()))
            ->add('orderBy', 'b.name ASC');
        
        //$qb->setParameter(1, $service);
        $query  = $qb->getQuery();      
        return $query->getResult();
    }

}

