<?php
namespace Project\Repository;
 
use Doctrine\ORM\EntityRepository;
use Project\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class DocumentList extends EntityRepository
{
    public function findByProjectId($project_id, array $params=array(), $array=false) {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('d.filename, d.size, d.documentListId, d.subid, d.created, u.forename, u.surname, e.extension')
            ->from('Project\Entity\DocumentList', 'd')
            ->join('d.extension', 'e')
            ->join('d.user', 'u')
            ->where('d.project=?1')
            ->orderBy('d.created', 'DESC')
            ->setParameter(1, $project_id);
        
        if (!empty($params['categoryId'])) {
            $qb
                ->andWhere('d.category=?2')
                ->setParameter(2, $params['categoryId']);
        }
        
        if (!empty($params['subid'])) {
            $qb
                ->andWhere('d.subid=?3')
                ->setParameter(3, $params['subid']);
        }
        
        $query  = $qb->getQuery();      
        
        if ($array===true) {
            return  $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }
        
        return $query->getResult();
    } 
}

