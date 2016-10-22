<?php
namespace Application\Repository;
 
use Doctrine\ORM\EntityRepository;
use Application\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Audit extends EntityRepository
{
    public function findByClientId($client_id, $array=false, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('u.username, u.avatar_name, u.email, u.forename, u.surname, '
                    . 'a.created, a.data, '
                    . 'at.name AS atName, at.icon, at.box, at.auditTypeId, '
                    . 'c.name AS cName, c.clientId, '
                    . 'p.name AS pName, p.projectId, '
                    . 's.name AS sName, s.spaceId, '
                    . 'd.name AS dName, '
                    . 'pr.model')
            ->from('Application\Entity\Audit', 'a')
            ->innerJoin('a.auditType', 'at')
            ->innerJoin('a.user', 'u')
            ->innerJoin('a.client', 'c')
            ->leftJoin('a.project', 'p')
            ->leftJoin('a.space', 's')
            ->leftJoin('a.documentCategory', 'd')
            ->leftJoin('a.product', 'pr')
            ->where('c.clientId = '.$client_id)
            ->orderBy('a.created', 'DESC')
            
                ;
        
        if (isset($params['max'])) {
            if (preg_match('/^[\d]+$/',$params['max'])) {
                $queryBuilder->setMaxResults($params['max']);
            }
        }
        
        if (isset($params['auto'])) {
            $queryBuilder->andWhere('at.auto = '.(empty($params['auto'])?0:1));
        }
        
        $query  = $queryBuilder->getQuery();
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();

    }
    
    
    public function findByProjectId($project_id, $array=false, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('u.username, u.avatar_name, u.email, u.forename, u.surname, '
                    . 'a.created, a.data, '
                    . 'at.name AS atName, at.icon, at.box, at.auditTypeId, '
                    . 'c.clientId, '
                    . 'p.name AS pName, p.projectId, '
                    . 's.name AS sName, s.spaceId, '
                    . 'd.name AS dName, '
                    . 'pr.model')
            ->from('Application\Entity\Audit', 'a')
            ->innerJoin('a.auditType', 'at')
            ->innerJoin('a.user', 'u')
            ->innerJoin('a.client', 'c')
            ->innerJoin('a.project', 'p')
            ->leftJoin('a.space', 's')
            ->leftJoin('a.documentCategory', 'd')
            ->leftJoin('a.product', 'pr')
            ->where('p.projectId = '.$project_id)
            ->orderBy('a.created', 'DESC')
            
                ;
        
        if (isset($params['max'])) {
            if (preg_match('/^[\d]+$/',$params['max'])) {
                $queryBuilder->setMaxResults($params['max']);
            }
        }
        
        if (isset($params['auto'])) {
            $queryBuilder->andWhere('at.auto = '.(empty($params['auto'])?0:1));
        }
        
        $query  = $queryBuilder->getQuery();
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();

    }
    
    
    public function findPaginateByClientId($client_id, $length=10, $start=1, array $params=array()) {
        
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('a')
            ->from('Application\Entity\Audit', 'a')
            ->innerJoin('a.auditType', 'at')
            ->innerJoin('a.user', 'u')
            ->innerJoin('a.client', 'c')
            ->innerJoin('a.project', 'p')
            ->leftJoin('a.space', 's')
            ->leftJoin('a.documentCategory', 'd')
            ->leftJoin('a.product', 'pr')
            ->where('a.client = '.$client_id)
            ->orderBy('a.created', 'DESC')
            
                ;
        
        if (isset($params['auto'])) {
            $queryBuilder->andWhere('at.auto = '.(empty($params['auto'])?0:1));
        }
        
        if (!empty($params['projectId'])) {
            $queryBuilder->andWhere('a.project = '.$params['projectId']);
        }
        
        
        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $queryBuilder->andWhere('at.name LIKE :keyword')
                    ->setParameter('keyword', '%'.$keyword.'%');
        }    /**/    
        
        /*
         * Ordering
         */
        if (!empty($params['orderBy'])) {
            foreach ($params['orderBy'] as $name=>$dir) {
                switch($name){
                    case 'created':
                        $queryBuilder->add('orderBy', 'a.created '.$dir);
                        break;
                    case 'type':
                        $queryBuilder->add('orderBy', 'at.name '.$dir);
                        break;
                    case 'user':
                        $queryBuilder->add('orderBy', 'u.forename '.$dir);
                        $queryBuilder->add('orderBy', 'u.surname '.$dir);
                        break;
                }
            }
        }
        /**/  
        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($queryBuilder))
        );

        $start = (floor($start / $length)+1);
        
        
        $paginator
            ->setCurrentPageNumber($start)
            ->setItemCountPerPage($length);
       
        return $paginator;
        
        

    }
    
    
    

}

