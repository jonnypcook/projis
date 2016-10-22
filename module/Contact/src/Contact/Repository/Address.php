<?php

namespace Contact\Repository;
 
use Doctrine\ORM\EntityRepository;
use Contact\Entity;
 
class Address extends EntityRepository
{
    public function findByAddressId($address_id) {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery("SELECT a FROM Contact\Entity\Address a WHERE a.addressId=".$address_id);
        return $query->getResult();
    }

    public function findByClientContact($client_id) {
        // First get the EM handle
        // and call the query builder on it
        $qb  = $this->getEntityManager()->createQueryBuilder();
        $qb2 = $qb;
        $qb2->select('ad.addressId')
            ->from('Contact\Entity\Contact', 'c')
            ->innerJoin('c.address', 'ad')
            ->where('c.client = '.$client_id);
        
        $qb  = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('Contact\Entity\Address', 'a')
            ->where($qb->expr()->in('a.addressId', $qb2->getDQL()))
            ->add('orderBy', 'a.postcode ASC');
        
        //$qb->setParameter(1, $service);
        $query  = $qb->getQuery();      
        return $query->getResult();
    }
    
    
    public function findByClientId($client_id, $array=false) {
        // First get the EM handle
        // and call the query builder on it
        $qb  = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('Contact\Entity\Address', 'a')
            ->where('a.client = '.$client_id)
            ->add('orderBy', 'a.postcode ASC');
        
        //$qb->setParameter(1, $service);
        $query  = $qb->getQuery();
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    }
}

