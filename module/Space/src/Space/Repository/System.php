<?php

namespace Space\Repository;
 
use Doctrine\ORM\EntityRepository;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class System extends EntityRepository
{
    public function findBySpaceId($spaceId, $params=array()) {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery("SELECT ".
                (!empty($params['array'])?
                "s.cpu, s.ppu, s.ppuTrial, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.systemId, s.label, s.attributes, s.locked,  "
                . "sp.spaceId, "
                . "p.productId, p.model, p.eca, p.pwr, p.mcd, p.description AS pdesc, "
                . "l.legacyId, l.description,"
                . "pt.typeId, "
                . "c.name as category ":
                "s "
                )
                . "FROM Space\Entity\System s "
                . "JOIN s.space sp "
                . "JOIN s.product p "
                . "JOIN p.type pt "
                . "LEFT JOIN s.legacy l "
                . "LEFT JOIN l.category c "
                . "WHERE sp.spaceId = {$spaceId} "
                . (!empty($params['locked'])?'AND s.locked=1 ':'')
                . (!empty($params['type'])?'AND pt.typeId='.$params['type'].' ':'')
                . "ORDER BY p.model DESC");
        
        if (!empty($params['array'])) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    }
    
    public function findBySystemId($systemId, $params=array()) {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery("SELECT ".
                (!empty($params['array'])?
                "s.cpu, s.ppu, s.ppuTrial, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.systemId, s.label, s.attributes, "
                . "sp.spaceId, "
                . "p.productId, p.model, p.eca, p.pwr, p.mcd, "
                . "pt.service, pt.typeId, "
                . "l.legacyId, l.description ":
                "s "
                )
                . "FROM Space\Entity\System s "
                . "JOIN s.space sp "
                . "JOIN s.product p "
                . "JOIN p.type pt "
                . "LEFT JOIN s.legacy l "
                . "WHERE s.systemId = {$systemId}");
        
        if (!empty($params['array'])) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    }
    
    public function findByProjectId($projectId, $params=array()) {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery("SELECT ".
                (!empty($params['array'])?
                "s.cpu, s.ppu, s.ppuTrial, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.systemId, s.label, "
                . "sp.spaceId, "
                . "p.productId, p.model, p.eca, p.pwr, p.mcd, "
                . "pt.service, "
                . "b.name, b.buildingId, "
                . "l.legacyId, l.description ":
                "s "
                )
                . "FROM Space\Entity\System s "
                . "JOIN s.space sp "
                . "JOIN s.product p "
                . "LEFT JOIN sp.building b "
                . "JOIN sp.project pr "
                . "JOIN p.type pt "
                . "LEFT JOIN s.legacy l "
                . "WHERE pr.projectId = {$projectId}");
        
        if (!empty($params['array'])) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    }
    
    public function findByProjectIdProductId($projectId, $productId, $params=array()) {
        // First get the EM handle
        // and call the query builder on it
        $query = $this->_em->createQuery("SELECT ".
                (!empty($params['array'])?
                "s.cpu, s.ppu, s.ppuTrial, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.systemId, s.label, "
                . "sp.spaceId, "
                . "p.productId, p.model, p.eca, p.pwr, p.mcd, "
                . "pt.service, "
                . "b.name, b.buildingId, "
                . "l.legacyId, l.description ":
                "s "
                )
                . "FROM Space\Entity\System s "
                . "JOIN s.space sp "
                . "JOIN s.product p "
                . "LEFT JOIN sp.building b "
                . "JOIN sp.project pr "
                . "JOIN p.type pt "
                . "LEFT JOIN s.legacy l "
                . "WHERE pr.projectId = {$projectId} AND "
                . "p.productId = {$productId}");
        
        if (!empty($params['array'])) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    }
    
    
    
}

