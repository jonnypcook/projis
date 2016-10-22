<?php

namespace Resource\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Project\Entity;
use Zend\Paginator\Paginator;


class ResourceActivity extends EntityRepository
{
    /**
     * @param int   $offset
     * @param int   $limit
     * @param array $params
     * @return ORMPaginator
     *
     */
    public function findByPaginate($offset = 0, $limit = 10, $params = Array())
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder
            ->select('ra')
            ->from('Resource\Entity\ResourceActivity', 'ra')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ( !empty($params) )
        {
            $queryBuilder->where('project', $params['jid']);
        }

        $query = $queryBuilder->getQuery();

        $paginator = new ORMPaginator($query);

        return $paginator;
    }

    public function findPaginateByProjectId($project_id, $length = 10, $start = 1, array $params = array())
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('ra')
            ->from('ResourceActivity', 'ra')
            ->leftJoin('ra.project', 'p')
            ->where('p.project = :projectId')
            ->setParameter("projectId", $project_id);


        /*
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        if ( !empty($params['keyword']) )
        {
            $keyword = $params['keyword'];
            if ( preg_match('/^[\d]+$/', trim($keyword)) )
            {
                $keyword = (int)$keyword;
                $queryBuilder->andWhere('p.name LIKE :pname')
                    ->setParameter('pname', '%' . $keyword . '%');
            }
            else
            {
                $queryBuilder->andWhere('p.model LIKE :name')
                    ->setParameter('name', '%' . trim(preg_replace('/[*]+/', '%', $keyword), '%') . '%');
            }
        }

        /*
         * Ordering
         */
        if ( !empty($params['orderBy']) )
        {
            foreach ( $params['orderBy'] as $name => $dir )
            {
                switch ( $name )
                {
                    case 'serialId':
                        $queryBuilder->add('orderBy', 's.serialId ' . $dir);
                        break;
                    case 'productId':
                        $queryBuilder->add('orderBy', 'p.model ' . $dir);
                        break;
                    case 'spaceId':
                        $queryBuilder->add('orderBy', 'sp.name ' . $dir);
                        break;
                    case 'created':
                        $queryBuilder->add('orderBy', 's.created ' . $dir);
                        break;
                }
            }
        }

        /**/
        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($queryBuilder))
        );

        $start = (floor($start / $length) + 1);


        $paginator
            ->setCurrentPageNumber($start)
            ->setItemCountPerPage($length);

        return $paginator;

    }

    public function findResources($project_id, $includeInvoices = false)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('ra')
            ->from('Resource\Entity\ResourceActivity', 'ra')
            ->where('ra.referenceType != :rType')
            ->andWhere('ra.project = :projectId')
            ->setParameter("rType", "invoice")
            ->setParameter('projectId', $project_id);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function findInvoices($project_id)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('ra')
            ->from('Resource\Entity\ResourceActivity', 'ra')
            ->where('ra.referenceType = :rType')
            ->andWhere('ra.project = :projectId')
            ->setParameter("rType", "invoice")
            ->setParameter('projectId', $project_id);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    /**
     * Get activities between start and end completed date
     *
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function getCosList($start_date, $end_date)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('ra')
            ->from('Resource\Entity\ResourceActivity', 'ra')
            ->join('Project\Entity\Project', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
            ->where('p.completed BETWEEN :start_date AND :end_date')
            ->andWhere('ra.referenceType <> \'invoice\'')
            ->setParameter('start_date', $start_date)
            ->setParameter('end_date', $end_date)
            ->orderBy('ra.referenceType', 'ASC')
            ->addOrderBy('p.completed', 'DESC');


        $query = $qb->getQuery();

        //echo $query->getSQL(); exit;
        return $query->getResult();
    }

    public function getInvoicesList($start_date, $end_date)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('ra')
            ->from('Resource\Entity\ResourceActivity', 'ra')
            ->join('Project\Entity\Project', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
            ->where('p.completed BETWEEN :start_date AND :end_date')
            ->andWhere('ra.referenceType = \'invoice\'')
            ->setParameter('start_date', $start_date)
            ->setParameter('end_date', $end_date)
            ->orderBy('ra.reference', 'DESC');
        //->addOrderBy('p.completed', 'DESC');


        $query = $qb->getQuery();

        //echo $query->getSQL(); exit;
        return $query->getResult();
    }

    /**
     * Get activities between start and completed date
     *
     * @param $params
     * @return array
     */
    public function getWipList($contracted_date)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('ra')
            ->from('Resource\Entity\ResourceActivity', 'ra')
            ->join('Project\Entity\Project', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
            ->innerJoin('p.status', 'ps')
            ->where('p.contracted >= :contracted')
            ->andWhere('p.status BETWEEN 40 AND 45')
            ->andWhere('ra.referenceType <> \'invoice\'')
            ->setParameter('contracted', $contracted_date)
            ->orderBy('ra.referenceType', 'ASC')
            ->addOrderBy('p.contracted', 'DESC');


        $query = $qb->getQuery();

        //echo $query->getSQL(); exit;
        return $query->getResult();
    }


    ###########################################################################
    ## Function Below are for Sales Activity Report on Resource Activities page
    ###########################################################################

    /**
     * Get Jobs Without Invoice
     *
     * @return array
     */
    public function getResourcesWithoutInvoice($start_dt = '', $end_dt = '', $filter_type = '', $client = '', $owner = '')
    {

        $start_date = '';
        $end_date   = '';
        if ( !empty($start_dt) )
        {
            $start_date = date('Y-m-d', strtotime($start_dt));  //$date_arr[2] . '-' . $date_arr[1] . '-' . $date_arr[0];
        }

        if ( !empty($end_dt) )
        {
            $end_date = date('Y-m-d', strtotime($end_dt));  //$date_arr[2] . '-' . $date_arr[1] . '-' . $date_arr[0];
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select('ra')
            ->from('Resource\Entity\ResourceActivity', 'ra');

        if ( $filter_type == 'wip' )
        {
            $qb->join('Project\Entity\Project', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
                ->innerJoin('p.status', 'ps')
                ->where('p.completed >= :completed')
                ->andWhere('p.status BETWEEN 40 AND 45')
                ->andWhere('ra.referenceType <> \'invoice\'')
                ->setParameter('completed', $end_date)
                ->orderBy('ra.referenceType', 'ASC')
                ->addOrderBy('p.completed', 'DESC');
        }
        elseif ( $filter_type == 'cos' )
        {
            $qb->join('Project\Entity\Project', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
                ->where('p.completed BETWEEN :start_date AND :end_date')
                ->andWhere('ra.referenceType <> \'invoice\'')
                ->setParameter('start_date', $start_date)
                ->setParameter('end_date', $end_date)
                ->orderBy('ra.referenceType', 'ASC')
                ->addOrderBy('p.completed', 'DESC');
        }
        else
        {
            $qb->join('Project\Entity\Project', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project');
            $qb->where('ra.referenceType <> \'invoice\'')->distinct('ra.project');
        }

        $result = [];

        // for populating subfilters with clients and owners
        if ( $filter_type == 'wip' || $filter_type == 'cos' )
        {
            $q = $qb->getQuery();
            $r = $q->getResult();
            $result['users'] = $r;
        }

        if ( !empty($client) )
        {
            $qb->andWhere('p.client = :client')
                ->setParameter('client', $client);
        }

        if ( !empty($owner) )
        {
            $qb->join('p.client', 'cl')
                ->andWhere('cl.user = :owner')
                ->setParameter('owner', $owner);
        }

        $query = $qb->getQuery();

        $jobs = $query->getArrayResult();
        $ids  = [];
        foreach ( $jobs as $key => $job )
        {
            $ids[] = $job['project'];
        }

        $project_ids = array_unique($ids);

        $grand_totals = [
            'grand_billed'   => 0,
            'grand_costs'    => 0,
            'grand_margin'   => 0,
            'grand_invoices' => 0,
            'grand_others'   => 0,
            'grand_counts'   => count($project_ids)
        ];

        if ( !empty($project_ids) )
        {
            foreach ( $project_ids as $pid )
            {
                $project = $this->_em->getRepository('Project\Entity\Project')->findByProjectId($pid);

                // Start : Get resources
                $qb = $this->_em->createQueryBuilder();
                $qb
                    ->select('ra, cc')
                    ->from('Resource\Entity\ResourceActivity', 'ra')
                    ->join('ra.costCode', 'cc')
                    ->where('ra.referenceType <> \'invoice\'')
                    ->andWhere('ra.project = :project')
                    ->setParameter('project', $pid)
                    ->orderBy('cc.name', 'ASC');

                $res       = $qb->getQuery()->getResult();
                $resources = [];
                if ( !empty($res) )
                {
                    foreach ( $res as $r )
                    {
                        $resources[$r->getCostCode()->getCostCodeId()][] = $r;
                    }
                }

                // Get invoices total
                $qb = $this->_em->createQueryBuilder();
                $qb
                    ->select('ra, cc, r')
                    ->from('Resource\Entity\ResourceActivity', 'ra')
                    ->join('ra.costCode', 'cc')
                    ->join('ra.resource', 'r')
                    ->where('ra.project = :project')
                    ->setParameter('project', $pid);

                $res = $qb->getQuery()->getResult();

                $totals             = [];
                $totals['invoices'] = 0;
                $totals['others']   = 0;

                if ( !empty($res) )
                {
                    foreach ( $res as $resource )
                    {
                        $totals['quantity'] += $resource->getQuantity();
                        $totals['rate'] += $resource->getRate();
                        $totals['total'] += $resource->getQuantity() * $resource->getRate();
                        if ( $resource->getResource()->getName() == 'Invoice' )
                        {
                            $totals['invoices'] += ($resource->getRate() * $resource->getQuantity());
                        }
                        else
                        {
                            $totals['others'] += $resource->getQuantity() * $resource->getRate();
                        }
                    }
                }
                $discount = ($project->getMcd());

                $query  = $this->_em->createQuery('SELECT  p.mcd, p.productId, p.model, p.eca, p.attributes, pt.service, pt.name AS productType, pt.name as type, s.ppu, s.cpu, b.name as brand, '
                    . 'SUM(s.quantity) AS quantity, '
                    . 'SUM(s.ppu*s.quantity) AS price, '
                    . 'SUM(ROUND((s.ppu * (1 - (' . $discount . ' * p.mcd))),2) * s.quantity) AS priceMCD, '
                    . 'SUM(s.cpu*s.quantity) AS cost '
                    . 'FROM Space\Entity\System s '
                    . 'JOIN s.space sp '
                    . 'JOIN s.product p '
                    . 'JOIN p.brand b '
                    . 'JOIN p.type pt '
                    . 'WHERE sp.project=' . $pid . ' '
                    . 'GROUP BY s.product, s.ppu '
                    . 'ORDER BY s.product');
                $system = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

                $totals['expected_price']        = 0;
                $totals['expected_cost']         = 0;
                $totals['expected_price_mcd']    = 0;
                $totals['expected_qty']          = 0;
                $totals['expected_margin_value'] = 0;
                if ( !empty($system) )
                {
                    foreach ( $system as $item )
                    {
                        $totals['expected_price'] += $item['price'];
                        $totals['expected_cost'] += $item['cost'];
                        $totals['expected_price_mcd'] += $item['priceMCD'];
                        $totals['expected_qty'] += $item['quantity'];
                        $totals['expected_margin_value'] += ($item['priceMCD'] - $item['cost']);
                    }
                }

                $grand_totals['grand_billed'] += $totals['expected_price'];
                $grand_totals['grand_costs'] += $totals['expected_cost'];
                $grand_totals['grand_margin'] += $totals['expected_margin_value'];
                $grand_totals['grand_invoices'] += $totals['invoices'];
                $grand_totals['grand_others'] += $totals['others'];

                $result[$pid] = Array(
                    'project'   => $this->_em->getRepository('Project\Entity\Project')->findByProjectId($pid),
                    'resources' => $resources,
                    'totals'    => $totals
                );
            }
        }

        $result['grand_totals'] = $grand_totals; // grand totals to be used in top of report

        return $result;
    }

}