<?php

namespace Job\Repository;
 
use Doctrine\ORM\EntityRepository;
use Project\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Serial extends EntityRepository
{
    public function findPaginateByProjectId($project_id, $length=10, $start=1, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('s')
            ->from('Job\Entity\Serial', 's')
            ->leftJoin('s.system', 'sys')
            ->leftJoin('sys.space', 'sp')
            ->leftJoin('sys.product', 'p')
            ->where('s.project = :projectId')
            ->setParameter("projectId", $project_id);
        

        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            if (preg_match('/^[\d]+$/', trim($keyword))) {
                $keyword = (int)$keyword;
                $queryBuilder->andWhere('s.serialId LIKE :sid')
                ->setParameter('sid', '%'.$keyword.'%');
            } else {
                $queryBuilder->andWhere('p.model LIKE :name')
                ->setParameter('name', '%'.trim(preg_replace('/[*]+/','%',$keyword),'%').'%');
            }
        }        
        
        /*
         * Ordering
         */
        if (!empty($params['orderBy'])) {
            foreach ($params['orderBy'] as $name=>$dir) {
                switch($name){
                    case 'serialId':
                        $queryBuilder->add('orderBy', 's.serialId '.$dir);
                        break;
                    case 'productId':
                        $queryBuilder->add('orderBy', 'p.model '.$dir);
                        break;
                    case 'spaceId':
                        $queryBuilder->add('orderBy', 'sp.name '.$dir);
                        break;
                    case 'created':
                        $queryBuilder->add('orderBy', 's.created '.$dir);
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
    
    
    public function findByUserId($user_id, $array=false, array $params=array()) {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from('Project\Entity\Project', 'p')
            ->innerJoin('p.client', 'c')
            ->innerJoin('p.status', 's')
            ->where('c.user = '.$user_id)
            ->orderBy('p.created', 'DESC')
            
                ;
        
        if (isset($params['project'])) {
            if ($params['project']==true) {
                $queryBuilder
                        ->andWhere('s.job=0')
                        ->andWhere('s.halt=0')
                        ->andWhere('p.cancelled=false');
            }
        }
        
        
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

}

