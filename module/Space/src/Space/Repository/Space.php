<?php

namespace Space\Repository;
 
use Doctrine\ORM\EntityRepository;
use Space\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Space extends EntityRepository
{
    public function findByProjectId($project_id, $params=array()) {
        $query = $this->_em->createQuery("SELECT s FROM Space\Entity\Space s JOIN s.project p WHERE p.projectId=".$project_id.(!empty($params['root'])?' AND s.root=1 ':'')." ORDER BY s.building ASC");
        return $query->getResult();
    }
    
    public function findByBuildingId($building_id, $project_id, $array=false, array $config=array()) {
        // First get the EM handle
        // and call the query builder on it
        $deleted = !empty($array['deleted']);
        
        // check for aggregate function mode
        $aggs = array();
        if (!empty($config['agg'])) {
            foreach ($config['agg'] as $name=>$value) {
                switch ($name) {
                    case 'ppu':
                        $aggs[] = 'SUM(s.ppu * s.quantity) AS ppu';
                        break;
                    case 'cpu':
                        $aggs[] = 'SUM(s.cpu * s.quantity) AS cpu';
                        break;
                    case 'quantity':
                        $aggs[] = 'SUM(s.quantity) AS quantity';
                        break;
                }
            }
            
            
        }
        
        if (!empty($aggs)) {
            $array = true;
            $query = $this->_em->createQuery("SELECT sp.spaceId, sp.name, "
                . implode (', ', $aggs)." "
                . "FROM Space\Entity\Space sp "
                . "LEFT JOIN Space\Entity\System s  WHERE sp.spaceId = s.space "
                . "WHERE "
                . "sp.project = {$project_id} AND "
                . "sp.building = {$building_id} "
                . ($deleted?'':' AND sp.deleted != true ')
                . "GROUP BY sp.spaceId");
                
        } else {
            $query = $this->_em->createQuery("SELECT s FROM Space\Entity\Space s JOIN s.project p JOIN s.building b WHERE p.projectId=".$project_id." AND b.buildingId=".$building_id.($deleted?'':' AND s.deleted != true')." ORDER BY s.building ASC");
        }
        

        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    }

}

