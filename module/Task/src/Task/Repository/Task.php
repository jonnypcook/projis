<?php

namespace Task\Repository;
 
use Doctrine\ORM\EntityRepository;
use Project\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Task extends EntityRepository
{
    public function findPaginateByUserId($user_id, $length=10, $start=1, array $params=array()) {
        $development = !empty($params['development']);
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('t')
            ->from('Task\Entity\Task', 't')
            ->join('t.taskType', 'tt')
            ;
        
        if ($development) {
            $queryBuilder
                ->andWhere("t.taskType=3"); // development task
        } else {
            $queryBuilder
                ->innerJoin("t.users", "u", "WITH", "(u=:userid OR t.user=:userid)")
                ->setParameter("userid", $user_id);
        }
        
        // TODO: check for additional parameters
        if (!empty($params['taskStatus'])) {
            $queryBuilder->andWhere('t.taskStatus=:taskStatus')->setParameter('taskStatus', $params['taskStatus']);
        }
        
        /* 
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $queryBuilder->andWhere('t.description LIKE :desc')->setParameter('desc', '%'.$keyword.'%');
            $queryBuilder->andWhere('tt.name LIKE :name')->setParameter('name', '%'.trim(preg_replace('/[*]+/','%',$keyword),'%').'%');
        }        
        
        /*
         * Ordering
         */
        if (!empty($params['orderBy'])) {
            foreach ($params['orderBy'] as $name=>$dir) {
                switch($name){
                    case 'taskId':
                        $queryBuilder->add('orderBy', 't.taskId '.$dir);
                        break;
                    case 'taskType':
                        $queryBuilder->add('orderBy', 't.taskType '.$dir);
                        break;
                    case 'user':
                        $queryBuilder->add('orderBy', 't.user '.$dir);
                        break;
                    case 'required':
                        $queryBuilder->add('orderBy', 't.required '.$dir);
                        break;
                    case 'created':
                        $queryBuilder->addOrderBy('t.created ', $dir);
                        break;
                    case 'taskStatus':
                        $queryBuilder->add('orderBy', 't.taskStatus '.$dir);
                        $queryBuilder->addOrderBy('t.required ', 'asc');
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

