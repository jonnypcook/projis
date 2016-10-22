<?php
namespace Application\Repository;
 
use Doctrine\ORM\EntityRepository;
use Application\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Activity extends EntityRepository
{
    public function findByClientId($client_id, $array=false, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('u.username, u.avatar_name, u.email, u.forename, u.surname, u.userId, u.picture, '
                    . 'a.created, a.data, a.startDt, a.endDt, a.note, '
                    . 'at.name AS atName, at.activityTypeId, '
                    . 'c.clientId, c.name as cName, '
                    . 'p.name AS pName, p.projectId')
            ->from('Application\Entity\Activity', 'a')
            ->innerJoin('a.activityType', 'at')
            ->innerJoin('a.user', 'u')
            ->innerJoin('a.client', 'c')
            ->leftJoin('a.project', 'p')
            ->where('c.clientId = '.$client_id)
            ->orderBy('a.startDt', 'DESC')
            
                ;
        
        if (isset($params['max'])) {
            if (preg_match('/^[\d]+$/',$params['max'])) {
                $queryBuilder->setMaxResults($params['max']);
            }
        }
        
        $query  = $queryBuilder->getQuery();
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();

    }
    
    public function findByUserId($user_id, $array=false, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('u.username, u.avatar_name, u.email, u.forename, u.surname, u.userId, u.picture, '
                    . 'a.created, a.data, a.startDt, a.endDt, a.note, '
                    . 'at.name AS atName, at.activityTypeId, '
                    . 'c.clientId, c.name as cName, '
                    . 'p.name AS pName, p.projectId')
            ->from('Application\Entity\Activity', 'a')
            ->innerJoin('a.activityType', 'at')
            ->innerJoin('a.user', 'u')
            ->leftJoin('a.client', 'c')
            ->leftJoin('a.project', 'p')
            ->where('a.user = '.$user_id)
            ->orderBy('a.startDt', 'DESC')
            
                ;
        
        if (isset($params['max'])) {
            if (preg_match('/^[\d]+$/',$params['max'])) {
                $queryBuilder->setMaxResults($params['max']);
            }
        }
        
        $query  = $queryBuilder->getQuery();
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();

    }
    
    public function findPaginateByUserId($user_id, $length=10, $start=1, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('a')
            ->from('Application\Entity\Activity', 'a')
            ->innerJoin('a.activityType', 'at')
            ->innerJoin('a.user', 'u')
            ->leftJoin('a.client', 'c')
            ->leftJoin('a.project', 'p')
            ->where('a.user = '.$user_id)
            ->orderBy('a.startDt', 'DESC');
        
        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            if (preg_match('/^[\d]+$/', trim($keyword))) {
                $queryBuilder->andWhere('p.projectId = :pid OR c.clientId = :pid')
                ->setParameter('pid', $keyword);
            } elseif (preg_match('/^([\d]+)[-]([\d]+)$/', trim($keyword), $matches)) {
                $queryBuilder->andWhere('p.projectId = :pid')
                ->setParameter('pid', $matches[2]);
            } else {
                $queryBuilder->andWhere('a.note LIKE :keyword OR at.name LIKE :keyword')
                        ->setParameter('keyword', '%'.$keyword.'%');
            }
        }    /**/    
        
        /*
         * Ordering
         */
        if (!empty($params['orderBy'])) {
            foreach ($params['orderBy'] as $name=>$dir) {
                switch($name){
                    case 'startDt':
                        $queryBuilder->add('orderBy', 'a.startDt '.$dir);
                        break;
                    case 'activityType':
                        $queryBuilder->add('orderBy', 'a.activityType '.$dir);
                        break;
                    case 'note':
                        $queryBuilder->add('orderBy', 'a.note '.$dir);
                        break;
                    case 'client':
                        $queryBuilder->add('orderBy', 'a.client '.$dir);
                        $queryBuilder->add('orderBy', 'a.project '.$dir);
                        break;
                    case 'duration':
                        $queryBuilder->add('orderBy', 'a.endDt - a.startDt '.$dir);
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
        
    public function findByProjectId($project_id, $array=false, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('u.username, u.avatar_name, u.email, u.forename, u.surname, u.userId, u.picture, '
                    . 'a.created, a.data, a.startDt, a.endDt, a.note, '
                    . 'at.name AS atName, at.activityTypeId, '
                    . 'c.clientId, '
                    . 'p.name AS pName, p.projectId')
            ->from('Application\Entity\Activity', 'a')
            ->innerJoin('a.activityType', 'at')
            ->innerJoin('a.user', 'u')
            ->innerJoin('a.client', 'c')
            ->innerJoin('a.project', 'p')
            ->where('p.projectId = '.$project_id)
            ->orderBy('a.startDt', 'DESC')
            
                ;
        
        if (isset($params['max'])) {
            if (preg_match('/^[\d]+$/',$params['max'])) {
                $queryBuilder->setMaxResults($params['max']);
            }
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
            ->from('Application\Entity\Activity', 'a')
            ->innerJoin('a.activityType', 'at')
            ->innerJoin('a.user', 'u')
            ->where('a.client = '.$client_id)
            ->orderBy('a.startDt', 'DESC');
        
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
            $queryBuilder->andWhere('a.note LIKE :keyword OR at.name LIKE :keyword')
                    ->setParameter('keyword', '%'.$keyword.'%');
        }    /**/    
        
        /*
         * Ordering
         */
        if (!empty($params['orderBy'])) {
            foreach ($params['orderBy'] as $name=>$dir) {
                switch($name){
                    case 'startDt':
                        $queryBuilder->add('orderBy', 'a.startDt '.$dir);
                        break;
                    case 'type':
                        $queryBuilder->add('orderBy', 'a.activityType '.$dir);
                        break;
                    case 'user':
                        $queryBuilder->add('orderBy', 'u.forename '.$dir);
                        $queryBuilder->add('orderBy', 'u.surname '.$dir);
                        break;
                    case 'note':
                        $queryBuilder->add('orderBy', 'a.note '.$dir);
                        break;
                    case 'duration':
                        $queryBuilder->add('orderBy', 'a.endDt - a.startDt '.$dir);
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

