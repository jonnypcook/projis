<?php

namespace Project\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Project\Entity;
use Zend\Paginator\Paginator;


class Project extends EntityRepository
{
    public function findByProjectId($project_id, array $params = array())
    {
        $project = $this->_em->find('Project\Entity\Project', $project_id);
        if ( !($project instanceof \Project\Entity\Project) )
        {
            return false;
        }

        // check the client_id for matches
        if ( !empty($params['client_id']) )
        {
            if ( $params['client_id'] != $project->getClient()->getClientId() )
            {
                return false;
            }
        }

        return $project;
    }

    public function findByClientId($clientId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('p')
            ->from('Project\Entity\Project', 'p')
            ->join('p.status', 's')
            ->where('p.client=?1')
            ->setParameter(1, $clientId);

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findPaginateByClientId($client_id, $length = 10, $start = 1, array $params = array())
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from('Project\Entity\Project', 'p')
            ->join('p.status', 's')
            ->where('p.client=?1')
            ->setParameter(1, $client_id);

        /**
         * check for project or job parameter
         */

        if ( !empty($params['project']) )
        {
            $queryBuilder
                ->andWhere('s.job=0')
                ->andWhere('p.cancelled!=1')
                ->andWhere('p.type != 3')
                ->andWhere('s.weighting<1');
        }
        elseif ( !empty($params['job']) )
        {
            $queryBuilder
                ->andWhere('p.type != 3')
                ->andWhere('(s.job=1) OR (s.job=0 AND s.weighting=1 AND s.halt=1)');
        }
        elseif ( !empty($params['trial']) )
        {
            $queryBuilder
                ->andWhere('p.type = 3');
        }


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
                $queryBuilder->andWhere('p.projectId LIKE :pid')
                    ->setParameter('pid', '%' . $keyword . '%');
            }
            else
            {
                $queryBuilder->andWhere('p.name LIKE :name')
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
                    case 'id':
                        $queryBuilder->add('orderBy', 'p.projectId ' . $dir);
                        break;
                    case 'name':
                        $queryBuilder->add('orderBy', 'p.name ' . $dir);
                        break;
                    case 'status':
                        $queryBuilder->add('orderBy', 's.statusId ' . $dir);
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


    public function findByUserId($user_id, $array = false, array $params = array())
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from('Project\Entity\Project', 'p')
            ->innerJoin('p.client', 'c')
            ->innerJoin('p.status', 's')
            ->where('c.user = ' . $user_id)
            ->orderBy('p.created', 'DESC');

        if ( isset($params['project']) )
        {
            if ( $params['project'] == true )
            {
                $queryBuilder
                    ->andWhere('s.job=0')
                    ->andWhere('s.halt=0')
                    ->andWhere('p.cancelled=false');
            }
        }


        if ( isset($params['max']) )
        {
            if ( preg_match('/^[\d]+$/', $params['max']) )
            {
                $queryBuilder->setMaxResults($params['max']);
            }
        }

        $query = $queryBuilder->getQuery();

        if ( $array === true )
        {
            return $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }

        return $query->getResult();

    }


    public function searchByName($keyword, array $params = array())
    {
        $arr = !empty($params['array']);

        $select = 'p';
        if ( $arr && is_array($params['array']) )
        {
            $select = implode(',', $params['array']);
        }
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select($select)
            ->from('Project\Entity\Project', 'p')
            ->join('p.status', 's')
            ->join('p.client', 'c')
            ->join('p.type', 't')
            ->where('p.name LIKE :name')
            ->orWhere('c.name LIKE :name')
            ->setParameter('name', '%' . trim(preg_replace('/[*]+/', '%', $keyword), '%') . '%')
            ->orderBy('c.clientId', 'DESC')
            ->orderBy('p.projectId', 'DESC');

        $query = $queryBuilder->getQuery();

        if ( $arr )
        {
            return $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }

        return $query->getResult();
    }

    public function findQuotationsByQuarter($quarter, $year)
    {
        $sql
            = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, c.client_id,
                      CONCAT(u.forename," ", u.surname) user_name,
                      d.document_list_id, SUM(sy.ppu * sy.quantity * sp.quantity) price, SUM(sy.cpu * sy.quantity * sp.quantity) cost, d.created, s.space_id
                    FROM `DocumentList` d
                        LEFT JOIN `Project` p
                          ON p.`project_id` = d.`project_id`
                        LEFT JOIN `Client` c
                          ON c.`client_id` = p.`client_id`
                        LEFT JOIN `User` u
                          ON u.`user_id` = c.`user_id`
                        LEFT JOIN `Space` s
                          ON s.`project_id` = p.`project_id`
                        LEFT JOIN `System` sy
                          ON sy.space_id = s.space_id
                    WHERE
                        QUARTER(d.`created`) = \'' . $quarter . '\' AND
                        YEAR(d.`created`) = \'' . $year . '\' AND
                        d.`document_category_id` = 5
                        GROUP BY d.document_list_id
                        ORDER BY d.created DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findProposalsByQuarter($quarter, $year)
    {
        $sql
            = 'SELECT
                    p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity * sp.quantity) price,
                    CONCAT(u.forename," ", u.surname) user_name, d.document_list_id, c.client_id, d.created,
                    SUM(sy.cpu * sy.quantity * sp.quantity) cost
                        FROM `DocumentList` d
                        LEFT JOIN `Project` p ON p.`project_id` = d.`project_id`
                        LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                        LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                        LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                        LEFT JOIN `User` u ON u.user_id = c.user_id
                        WHERE
                            QUARTER(d.`created`) = ' . $quarter . ' AND
                            YEAR(d.`created`) = ' . $year . ' AND
                            d.`document_category_id` IN (1,2,3)
                            GROUP BY d.document_list_id
                            ORDER BY d.created DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findAllByQuarter($quarter, $year)
    {
        $sql
            = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  p.created
                FROM `Project` p
                  LEFT JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  LEFT JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  LEFT JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  LEFT JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE QUARTER(p.`created`) = ' . $quarter . '
                    AND YEAR(p.`created`) = ' . $year . '
                GROUP BY p.project_id
                ORDER BY p.created DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function findWonByQuarter($quarter, $year)
    {
        $sql
            = 'SELECT
              p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity * sp.quantity) price,
              CONCAT(u.forename, " ", u.surname ) user_name, c.client_id, p.created,
              SUM(sy.cpu * sy.quantity * sp.quantity) cost
            FROM `Project` p
            LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
            LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
            LEFT JOIN `Space` sp ON sp.project_id = p.project_id
            LEFT JOIN `System` sy ON sy.space_id = sp.space_id
            INNER JOIN `User` u ON u.`user_id` = c.`user_id`
            where
                QUARTER(p.`contracted`) = ' . $quarter . ' AND
                YEAR(p.`contracted`) = ' . $year . ' AND
                p.`weighting` >=1
                GROUP BY p.project_id
                ORDER BY p.created DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findLostByQuarter($quarter, $year)
    {
        $sql
            = 'SELECT
                  p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity * sp.quantity) price,
                  CONCAT(u.forename, " ", u.surname) user_name, c.client_id, p.created,
                  SUM(sy.cpu * sy.quantity * sp.quantity) cost
                        FROM `Project` p
                        LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                        LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                        LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                        LEFT JOIN `User` u ON u.`user_id` = c.`user_id`
                        WHERE
                            QUARTER(p.`created`) = ' . $quarter . ' AND
                            YEAR(p.`created`) = ' . $year . ' AND
                            p.`cancelled` = 1
                            GROUP BY p.project_id
                                ORDER BY p.created DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findOpenByQuarter($quarter, $year)
    {
        $sql
            = 'SELECT
                  p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity * sp.quantity) price,
                  CONCAT(u.forename," ", u.surname) user_name, c.client_id, p.created,
                  SUM(sy.cpu * sy.quantity * sp.quantity) cost
                FROM `Project` p
                LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                LEFT JOIN `User` u ON u.`user_id` = c.`user_id`
                WHERE
                    QUARTER(p.`created`) = ' . $quarter . ' AND
                    YEAR(p.`created`) = ' . $year . ' AND
                    ps.`weighting`= 0 AND
                    ps.`halt` = 0
                GROUP BY p.project_id
                ORDER BY p.created DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findYearlySalesOutlook($user)
    {
        $from_date = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $to_date   = mktime(0, 0, 0, date('m') + 12, 31, date('Y'));
        $sql
                   = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed,
                  QUARTER(p.contracted) qtr
                FROM `Project` p
                  LEFT JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  LEFT JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  LEFT JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  LEFT JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.contracted is not null AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  (p.project_status_id BETWEEN 40 AND 45) AND
                  (p.contracted BETWEEN \'' . date('Y-m-d H:i:s', $from_date) . '\' AND \'' . date('Y-m-d H:i:s', $to_date) . '\') AND
                  p.test = 0 AND
                  p.exclude_from_reporting = 0
                GROUP BY p.project_id
                ORDER BY p.contracted DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findYearlyOutlookCustomerWise($user)
    {
        $from_date = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $to_date   = mktime(0, 0, 0, date('m') + 12, 31, date('Y'));
        $sql
                   = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed,
                  QUARTER(p.contracted) qtr
                FROM `Project` p
                  LEFT JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  LEFT JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  LEFT JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  LEFT JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.contracted is not null AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  (p.project_status_id BETWEEN 40 AND 45) AND
                  (p.contracted BETWEEN \'' . date('Y-m-d H:i:s', $from_date) . '\' AND \'' . date('Y-m-d H:i:s', $to_date) . '\') AND
                  p.test = 0 AND
                  p.exclude_from_reporting = 0
                GROUP BY p.project_id
                ORDER BY p.contracted DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    public function findJobs($jobId)
    {

        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('p')
            ->from('Project\Entity\Project', 'p')
            ->where($qb->expr()->between('p.status', 40, 100))
            ->andWhere('p.projectId=?1')
            ->setParameter(1, $jobId);

        /*
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select( 'p' )
            ->from( 'Project\Entity\Project', 'p' )
            ->join( 'p.status', 's' )
            ->where( 'p.client=?1' )
            ->setParameter( 1, $jobId );
        */

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findProducts(\Project\Entity\Project $project)
    {
        $em       = $this->_em;
        $discount = ($project->getMcd());
        $query    = $em->createQuery('SELECT  p.mcd, p.productId, p.model, p.eca, p.attributes, pt.service, pt.name AS productType, pt.name as type, s.ppu, s.cpu, b.name as brand, '
            . 'SUM(s.quantity * sp.quantity) AS quantity, '
            . 'SUM(s.ppu * s.quantity * sp.quantity) AS price, '
            . 'SUM(ROUND((s.ppu * (1 - (' . $discount . ' * p.mcd))),2) * s.quantity * sp.quantity) AS priceMCD, '
            . 'SUM(s.cpu * s.quantity * sp.quantity) AS cost '
            . 'FROM Space\Entity\System s '
            . 'JOIN s.space sp '
            . 'JOIN s.product p '
            . 'JOIN p.brand b '
            . 'JOIN p.type pt '
            . 'WHERE sp.project=' . $project->getProjectId() . ' '
            . 'GROUP BY s.product, s.ppu '
            . 'ORDER BY s.product');

        $system = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $system;
    }

    public function getUserJobs($year, $user_id)
    {
        $fromYear = $year;
        $toYear   = $year + 1;
        if ( in_array(date('m'), array(1, 2, 3)) )
        {
            $formYear = $year - 1;
            $toYear   = $year;
        }

        $fromDate = mktime(0, 0, 0, 4, 1, $fromYear);
        $toDate   = mktime(23, 59, 59, 3, 31, ($toYear));

        $sql
            = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  SUM(ROUND((Sy.ppu * (1 - (pr.mcd * p.mcd))),2) * Sy.quantity * Sp.quantity) AS priceMCD,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  Join `Product` pr
                    ON Sy.product_id = pr.product_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.contracted IS NOT NULL AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  (p.project_status_id BETWEEN 40 AND 45) AND
                  (p.contracted BETWEEN \'' . date('Y-m-d H:i:s', $fromDate) . '\' AND \'' . date('Y-m-d H:i:s', $toDate) . '\') AND
                  c.user_id = \'' . $user_id . '\'
                GROUP BY p.project_id
                ORDER BY p.contracted DESC';

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $result;
    }

    /**
     * Find jobs those are not having any activity added in resources activity
     *
     * @param string $filter_type
     * @param string $start_date
     * @param string $end_date
     * @param string $owner
     * @param string $client
     * @return array
     */
    public function findJobsWithoutActivities($filter_type = '', $start_dt = '', $end_dt = '', $owner = '', $client = '')
    {
        $start_date = '';
        $end_date   = '';
        if ( !empty($start_dt) )
        {
            $start_date = date('Y-m-d H:i:s', strtotime($start_dt));  //$date_arr[2] . '-' . $date_arr[1] . '-' . $date_arr[0];
        }

        if ( !empty($end_dt) )
        {
            $end_date = date('Y-m-d H:i:s', strtotime($end_dt));  //$date_arr[2] . '-' . $date_arr[1] . '-' . $date_arr[0];
        }

        $qb = $this->_em->createQueryBuilder();
        //echo $end_date;echo $start_date;
        $qb->select('p')
            ->from('Project\Entity\Project', 'p');

        if ( $filter_type == 'wip' )
        {
            $qb->leftJoin('Resource\Entity\ResourceActivity', 'ra', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
                ->where('p.completed >= :completed')
                ->andWhere('p.status BETWEEN 40 AND 45')
                ->andWhere('ra.project is NULL')
                ->setParameter('completed', $end_date)
                ->addOrderBy('p.contracted', 'DESC');
        }
        elseif ( $filter_type == 'cos' )
        {
            $qb->Join('Resource\Entity\ResourceActivity', 'ra', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
                ->where('p.completed BETWEEN :start_date AND :end_date')
                ->andWhere('p.status BETWEEN 40 and 45')
                ->andWhere('ra.project is NULL')
                ->setParameter('start_date', $start_date)
                ->setParameter('end_date', $end_date)
                ->addOrderBy('p.completed', 'DESC');
        }
        else
        {
            $qb->join('Resource\Entity\ResourceActivity', 'ra', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.projectId = ra.project')
                ->where('p.status BETWEEN 40 AND 45')
                ->andWhere('ra.project is NULL')
                ->addOrderBy('p.completed', 'DESC');
        }

        $result = [];

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

       //echo $query->getSQL();        exit;

        $projects = $query->getResult();

        $grand_totals = [
            'grand_billed'   => 0,
            'grand_costs'    => 0,
            'grand_margin'   => 0,
            'grand_invoices' => 0,
            'grand_others'   => 0
        ];

        if ( !empty($projects) )
        {
            foreach ( $projects as $project )
            {
                $discount = $project->getMcd();
                //echo $discount;                exit;

                $query  = $this->_em->createQuery('SELECT  p.mcd, p.productId, p.model, p.eca, p.attributes, pt.service, pt.name AS productType, pt.name as type, s.ppu, s.cpu, b.name as brand, '
                    . 'SUM(s.quantity * sp.quantity) AS quantity, '
                    . 'SUM(s.ppu*s.quantity * sp.quantity) AS price, '
                    . 'SUM(ROUND((s.ppu * (1 - (' . $discount . ' * p.mcd))),2) * s.quantity * sp.quantity) AS priceMCD, '
                    . 'SUM(s.cpu*s.quantity * sp.quantity) AS cost '
                    . 'FROM Space\Entity\System s '
                    . 'JOIN s.space sp '
                    . 'JOIN s.product p '
                    . 'JOIN p.brand b '
                    . 'JOIN p.type pt '
                    . 'WHERE sp.project=' . $project->getProjectId() . ' '
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
                //$grand_totals['grand_invoices'] += $totals['invoices'];
                //$grand_totals['grand_others'] += $totals['others'];

                $result['projects'][] = Array(
                    'project'   => $project,
                    'totals'    => $totals
                );
            }
        }

        //$result['grand_totals'] = $grand_totals; // grand totals to be used in top of report

        return $result;
    }
}

