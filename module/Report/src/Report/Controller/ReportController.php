<?php
namespace Report\Controller;

use Application\Controller\AuthController;
use Zend\View\Model\JsonModel;

class ReportController extends AuthController
{

    public function indexAction()
    {
        $this->setCaption( 'System Reports' );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select( 'r' )
            ->from( 'Report\Entity\Report', 'r' )
            ->join( 'r.group', 'g' )
            ->orderBy( 'g.groupId', 'ASC' );


        $query           = $qb->getQuery();
        $reports         = $query->getResult();
        $reportsFiltered = array();
        foreach ( $reports as $report )
        {
            if ( empty($reportsFiltered[$report->getGroup()->getName()]) )
            {
                $reportsFiltered[$report->getGroup()->getName()] = array(
                    'icon'   => $report->getGroup()->getIcon(),
                    'colour' => $report->getGroup()->getColour(),
                    'data'   => array()
                );
            }
            $reportsFiltered[$report->getGroup()->getName()]['data'][$report->getReportId()] = array(
                $report->getName(),
                $report->getDescription(),
            );
        }

        $this->getView()->setVariable( 'groups', $reportsFiltered );

        return $this->getView();
    }

    private function getReportData( \Report\Entity\Report $report, $options = array() )
    {
        $data = array();
        if ( $report->getReportId() == 5 )
        {
            $sql
                  = "SELECT
                c.`client_id`, p.`project_id`,
                c.`name` as `cname`,
                p.`name` as `pname`,
                t1.`price`,
                p.`propertyCount`,
                ROUND(t1.`price`/p.`propertyCount`, 2) as `ppp`
                FROM `Project` p
                INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                INNER JOIN (
                    SELECT SUM(ROUND((sys.`quantity` * sys.`ppu`)*(1-(p.`mcd` * pr.`mcd`)), 2)) AS `price`, p.`project_id`
                    FROM `System` sys
                    INNER JOIN `Product` pr ON pr.`product_id` = sys.`product_id`
                    inner Join `Space` s on s.`space_id` = sys.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    WHERE s.`deleted`!=1
                    GROUP BY s.`project_id`
                ) t1 ON t1.`project_id` = p.`project_id`
                WHERE
                    p.`propertyCount`>1 AND
                    p.`propertyCount` IS NOT NULL AND
                    p.`project_status_id`=1
                ORDER BY c.`client_id`, p.`client_id` ASC
                ";
            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();

            $data = $stmt->fetchAll();

            if ( !empty($options['headers']) )
            {
                $tmp   = array();
                $tmp[] = array( '"Project Reference"', '"Client Name"', '"Project Name"', 'Value', '"Property Count"', '"Price Per Property"' );
                foreach ( $data as $item )
                {
                    $tmp[] = array(
                        $item['client_id'] . '-' . $item['project_id'],
                        '"' . $item['cname'] . '"',
                        '"' . $item['pname'] . '"',
                        $item['price'],
                        $item['propertyCount'],
                        $item['ppp'],
                    );
                }

                return $tmp;
            }

        }
        elseif ( $report->getReportId() == 6 )
        {
            $users = array();
            $sql   = 'SELECT * FROM `User` WHERE `active`=1 ORDER BY forename';
            $stmt  = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data       = $stmt->fetchAll();
            $users['0'] = 'All Users';
            // Fetching users to filter reports, this will be displayed in the dropdown box
            if ( !empty($data) )
            {
                foreach ( $data as $item )
                {
                    $users[$item['user_id']] = $item['forename'] . ' ' . $item['surname'];
                }
            }

            $tm_to = time();
            $dt_to = date( 'Y-m-d H:i:s', $tm_to );

            $tm_from = mktime( 0, 0, 0, date( 'm', $tm_to ), 1, date( 'Y', $tm_to ) );
            $dt_from = date( 'Y-m-d H:i:s', $tm_from );

            $qtr          = ceil( date( 'm', $tm_to ) / 3 );
            $qtr_start_tm = mktime( 0, 0, 0, (3 * $qtr) - 2, 1, date( 'Y', $tm_to ) );
            $qtr_start_dt = date( 'Y-m-d H:i:s', $qtr_start_tm );
            $qtr_end_tm   = mktime( 0, 0, 0, (3 * $qtr) + 1, 1, date( 'Y', $tm_to ) );
            $qtr_end_dt   = date( 'Y-m-d H:i:s', $qtr_end_tm );

            $yr_start_tm = mktime( 0, 0, 0, 1, 1, date( 'Y', $tm_to ) );
            $yr_start_dt = date( 'Y-m-d H:i:s', $yr_start_tm );
            $yr_end_tm   = mktime( 0, 0, 0, 12, 31, date( 'Y', $tm_to ) );
            $yr_end_dt   = date( 'Y-m-d H:i:s', $yr_end_tm );

            $tm_mthprev_from = mktime( 0, 0, 0, date( 'm', $tm_to ) - 1, 1, date( 'Y', $tm_to ) );
            $dt_mthprev_from = date( 'Y-m-d H:i:s', $tm_mthprev_from );

            $tm_mthprev_to = mktime( 0, 0, 0, date( 'm', $tm_to ), 1, date( 'Y', $tm_to ) );
            $dt_mthprev_to = date( 'Y-m-d H:i:s', $tm_mthprev_to );


            $pivotDate = null;

            $dates = array();
            $stats = array();
            $graph = array(
                array( 'Month', 'New Project', 'Open Project', 'Sold Project' )
            );

            for ( $i = 0; $i < 12; $i++ )
            {
                $pivotDate         = mktime( 0, 0, 0, date( 'm' ) - $i, 1, date( 'Y' ) );
                $stats[$pivotDate] = array(
                    0, // Clients             - Array index : 0
                    0, // Contacts            - Array index : 1
                    0, // Quotations          - Array index : 2
                    0, // Proposals           - Array index : 3
                    0, // All Projects  #     - Array index : 4
                    0, // won projects  #     - Array index : 5
                    0, // lost projects #     - Array index : 6
                    0, // open project  #     - Array index : 7
                    0, // All Project Value   - Array index : 8
                    0, // Won projects Value  - Array index : 9
                    0, // Lost projects Value - Array index : 10
                    0, // Open projects Value - Array index : 11
                );

                $dates[$pivotDate] = array(
                    date( 'M Y', $pivotDate ),
                    count( $graph )
                );

                $graph[] = array( date( 'M Y', $pivotDate ), 0, 0, 0 );
            }

            $headings = Array(
                "Clients",
                "Contacts",
                "Quotations",
                "Proposals",
                "All Projects #",
                "Won Projects #",
                "Lost Projects #",
                "Open Projects #",
                "All Project Value",
                "Won Projects Value",
                "Lost Projects Value",
                "Open Project Value"
            );

            // Getting user id, if this is posted to the url,
            // this user id is posted in case of ajax call to filter report data based on user
            $user_id = $this->getRequest()->getPost( 'user_id' );

            // Start: clients Engagements in period
            if ( !empty($user_id) )
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(`created`) `mth`, YEAR(`created`) `yr`
                        from `Client`
                        where
                            `created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                            `user_id`=' . $user_id . '
                        GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(`created`) `mth`, YEAR(`created`) `yr`
                        from `Client`
                        where
                            `created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                        GROUP BY `mth`, `yr`';

            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][0] = $arr['cnt'];
                }
            }
            // End: Clients Engagements in period

            // Start : contacts Engagements in period
            if ( $user_id )
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(c.`created`) `mth`, YEAR(c.`created`) `yr`
                        from `Contact` c
                        inner join `Client` cl ON cl.`client_id` = c.`client_id`
                        where
                            c.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                            cl.`user_id`=' . $user_id . '
                        GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(c.`created`) `mth`, YEAR(c.`created`) `yr`
                        from `Contact` c
                        inner join `Client` cl ON cl.`client_id` = c.`client_id`
                        where
                            c.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                        GROUP BY `mth`, `yr`';
            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();

            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][1] = $arr['cnt'];
                }
            }
            // End: contacts in period

            // Start: Quotations in period
            if ( $user_id )
            {
                $sql
                    = 'select COUNT(d.`document_list_id`) `cnt`, MONTH(d.`created`) `mth`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` = 5 AND
                                c.`user_id` = ' . $user_id . '
                            GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select COUNT(d.`document_list_id`) `cnt`, MONTH(d.`created`) `mth`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` = 5
                            GROUP BY `mth`, `yr`';
            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][2] = $arr['cnt'];
                }
            }
            // End: Quotations in period

            // Start: Proposals Raised in period
            if ( $user_id )
            {
                $sql
                    = 'select COUNT(distinct d.`document_list_id`) `cnt`, MONTH(d.`created`) `mth`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` IN (1,2,3) AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select COUNT(distinct d.`document_list_id`) `cnt`, MONTH(d.`created`) `mth`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` IN (1,2,3)
                            GROUP BY `mth`, `yr`';
            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][3] = $arr['cnt'];
                }
            }
            // End: Proposals raised in period


            // Start: New projects in period
            if ( $user_id )
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                            GROUP BY `mth`, `yr`';
            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();

            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][4] = $arr['cnt'];
                }
            }
            // End: new projects in period

            // Start: won Projects in period
            if ( $user_id )
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`contracted`) `mth`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `mth`, `yr`, p.project_id';
            }
            else
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`contracted`) `mth`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1
                            GROUP BY `mth`, `yr`';
            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][5] = $arr['cnt'];
                }
            }
            // End: won projects in period

            // Start: lost projects in period
            if ( $user_id )
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                p.`cancelled` = 1 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                p.cancelled = 1
                            GROUP BY `mth`, `yr`';
            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][6] = $arr['cnt'];
                }
            }
            // End: lost projects in period

            // Start: Open projects in period
            if ( $user_id )
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting` = 0 AND
                                ps.`halt` = 0 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select COUNT(*) `cnt`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting` = 0 AND
                                ps.`halt` = 0
                            GROUP BY `mth`, `yr`';
            }


            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][7] = $arr['cnt'];
                }
            }
            // End: Open projects in period


            // start: Value All projects in period
            if ( $user_id )
            {
                $sql
                    = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                            AND c.`user_id`=' . $user_id . '
                        GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                        GROUP BY `mth`, `yr`';

            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][8] = $arr['price'];
                }
            }
            // End: Value All projects in period


            //Start: Total Won Projects value
            if ( $user_id )
            {
                $sql
                    = 'select Sum(sy.ppu * sy.quantity) `price`, MONTH(p.`contracted`) `mth`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON p.project_id = sp.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `mth`, `yr`';

            }
            else
            {
                $sql
                    = 'SELECT SUM(sy.ppu * sy.quantity) `price`, MONTH(p.`contracted`) `mth`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1
                            GROUP BY `mth`, `yr`';

            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();

            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][9] = $arr['price'];
                }
            }

            //echo '<pre>'; print_r($stats); exit;
            // End: Total Won Projects Value

            // Start: Lost Projects Value
            if ( $user_id )
            {
                $sql
                    = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.`cancelled` = 1 AND
                                c.`user_id`=' . $user_id . ' AND
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                                GROUP by mth, yr';
            }
            else
            {
                $sql
                    = 'select  SUM(sy.`ppu` * sy.`quantity`) `price`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                              p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                              p.cancelled = 1
                              GROUP BY `mth`, `yr`';
            }
            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][10] = $arr['price'];
                }
            }

            // End: Lost Projects Value

            // start: value open Projects in period
            if ( $user_id )
            {
                $sql
                    = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                            ps.`weighting` = 0 AND
                            ps.`halt`=0 AND
                            c.`user_id`=' . $user_id . '
                        GROUP BY `mth`, `yr`';
            }
            else
            {
                $sql
                    = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, MONTH(p.`created`) `mth`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                            ps.`weighting` = 0 AND
                            ps.`halt` = 0
                        GROUP BY `mth`, `yr`';

            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ( $data as $arr )
            {
                $period = mktime( 0, 0, 0, $arr['mth'], 1, $arr['yr'] );
                if ( isset($dates[$period]) )
                {
                    $stats[$period][11] = $arr['price'];
                }
            }
            // End: Value open Projects in period


            $sumArray = array();

            foreach ( $stats as $k => $subArray )
            {
                foreach ( $subArray as $id => $value )
                {
                    $sumArray[$id] += $value;
                }
            }

            $stats['total']    = $sumArray;
            $totals            = $this->getAllTotal();
            $stats['allTotal'] = $totals;

            return Array( 'stats' => $stats, 'dates' => $dates, 'headings' => $headings, 'users' => $users );
        }
        elseif ( $report->getReportId() == 7 )
        {
            if ( empty($options) )
            {
                $products = $this->getEntityManager()->getRepository( 'Product\Entity\Product' )->findBy( $options, array( 'productId' => 'DESC' ) );

            }
            else
            {
                $criteria = new \Doctrine\Common\Collections\Criteria();

                $criteria->where( $criteria->expr()->gte( 'productId', $options['productId'] ) )
                    ->orderBy( array( 'productId' => 'DESC' ) );

                $products = $this->getEntityManager()->getRepository( 'Product\Entity\Product' )->matching( $criteria );
            }

            return $products;
        }
        elseif ( $report->getReportId() == 8 )
        {
            return $this->getQuarterlyData( $report, $options );
        }
        elseif ( $report->getReportId() == 9 )
        {
            return $this->getSalesRatingData( $report, $options );
        }
        elseif ( $report->getReportId() == 10 )
        {
            return $this->getJobsRatingData( $report, $options );
        }
        elseif ( $report->getReportId() == 11 )
        {
            return $this->getSalesRatingByCustomer( $report, $options );
        }
        elseif ( $report->getReportId() == 12 )
        {
            return $this->getJobsRatingByCustomer( $report, $options );
        }
        elseif ( $report->getReportId() == 13 )
        {
            return $this->getOrderBookData( $report, $options );
        }
        elseif ( $report->getReportId() == 14 )
        {
            return $this->getOrderBookQuarterly( $report, $options );
        }
        else
        {
            throw new \Exception ( 'Unsupported report' );
        }

        return $data;
    }

    /**
     * Get total of all records
     */
    private function getAllTotal()
    {

        $totals = array(
            0, // Clients             - Array index : 0
            0, // Contacts            - Array index : 1
            0, // Quotations          - Array index : 2
            0, // Proposals           - Array index : 3
            0, // All Projects  #     - Array index : 4
            0, // won projects  #     - Array index : 5
            0, // lost projects #     - Array index : 6
            0, // open project  #     - Array index : 7
            0, // All Project Value   - Array index : 8
            0, // Won projects Value  - Array index : 9
            0, // Lost projects Value - Array index : 10
            0, // Open projects Value - Array index : 11
        );

        // Getting user id, if this is posted to the url,
        // this user id is posted in case of ajax call to filter report data based on user
        $user_id = $this->getRequest()->getPost( 'user_id', '' );

        // Start: clients Engagements in period
        if ( !empty($user_id) )
        {
            $sql
                = 'SELECT COUNT(*) `client` FROM Client c
                    WHERE
                      c.user_id =' . $user_id;
        }
        else
        {
            $sql = 'SELECT COUNT(*) `client` FROM Client';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );

        $stmt->execute();
        $data      = $stmt->fetch();
        $totals[0] = !empty($data['client']) ? $data['client'] : 0;

        // End: Clients Engagements in period

        // Start : contacts Engagements in period
        if ( $user_id )
        {
            $sql
                = 'SELECT COUNT(*) `contact`
                        from `Contact` c
                        inner join `Client` cl ON cl.`client_id` = c.`client_id`
                        where
                            cl.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'SELECT COUNT(*) `contact`
                        FROM `Contact` c
                        INNER JOIN `Client` cl ON cl.`client_id` = c.`client_id`';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data      = $stmt->fetch();
        $totals[1] = !empty($data['contact']) ? $data['contact'] : 0; // Contacts
        // End: contacts in period

        // Start: Quotations in period
        if ( $user_id )
        {
            $sql
                = 'SELECT COUNT(d.`document_list_id`) `quotation`
                            FROM `DocumentList` d
                            INNER JOIN `Project` p ON p.`project_id` = d.`project_id`
                            INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                            WHERE
                                d.`document_category_id` = 5 AND
                                c.`user_id` =' . $user_id;
        }
        else
        {
            $sql
                = 'select COUNT(d.`document_list_id`) `quotation`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            where
                                d.`document_category_id` = 5';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data      = $stmt->fetch();
        $totals[2] = !empty($data['quotation']) ? $data['quotation'] : 0;
        // End: Quotations in period

        // Start: Proposals Raised in period
        if ( $user_id )
        {
            $sql
                = 'select COUNT(distinct d.`document_list_id`) `proposals`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`document_category_id` IN (1,2,3) AND
                                c.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'select COUNT(distinct d.`document_list_id`) `proposal`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`document_category_id` IN (1,2,3)';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data      = $stmt->fetch();
        $totals[3] = !empty($data['proposal']) ? $data['proposal'] : 0;

        // End: Proposals raised in period


        // Start: Total all projects
        if ( $user_id )
        {
            $sql
                = 'select COUNT(*) `allProjects`
                            from `Project` p
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                c.`user_id`=' . $user_id;
        }
        else
        {
            $sql = 'select COUNT(*) `allProjects` from `Project`';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        $totals[4] = !empty($data['allProjects']) ? $data['allProjects'] : 0;

        // End: Total all projects

        // Start: Total won Projects
        if ( $user_id )
        {
            $sql
                = 'SELECT COUNT(*) `wonProjects`
                            FROM `Project` p
                            INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                            WHERE
                                ps.`weighting`=1 AND
                                c.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'SELECT COUNT(*) `wonProjects`
                            FROM `Project` p
                            INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                            where
                                ps.`weighting`=1';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data      = $stmt->fetch();
        $totals[5] = !empty($data['wonProjects']) ? $data['wonProjects'] : 0;
        // End: Total won projects

        // Start: Total lost projects
        if ( $user_id )
        {
            $sql
                = 'select COUNT(*) `lostProjects`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`cancelled` = 1 AND
                                c.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'select COUNT(*) `lostProjects`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.cancelled = 1';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        $totals[6] = !empty($data['lostProjects']) ? $data['lostProjects'] : 0;
        // End: lost projects in period

        // Start: Total Open projects
        if ( $user_id )
        {
            $sql
                = 'select COUNT(*) `openProjects`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                ps.`weighting` = 0 AND
                                ps.`halt` = 0 AND
                                c.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'select COUNT(*) `openProjects`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                ps.`weighting` = 0 AND
                                ps.`halt` = 0';
        }


        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data      = $stmt->fetch();
        $totals[7] = !empty($data['openProjects']) ? $data['openProjects'] : 0;
        // End: Open projects in period

        // start: Total all projects value
        if ( $user_id )
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `allValue`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                          c.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `allValue`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`';

        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data      = $stmt->fetch();
        $totals[8] = !empty($data['allValue']) ? $data['allValue'] : 0;
        // End: Value All projects in period

        //Start: Total Won Projects value
        if ( $user_id )
        {
            $sql
                = 'select Sum(sy.ppu * sy.quantity) `wonValue`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON p.project_id = sp.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                               p.contracted IS NOT NULL AND
                                ps.`weighting`=1 AND
                                c.`user_id`=' . $user_id;

        }
        else
        {
            $sql
                = 'SELECT SUM(sy.ppu * sy.quantity) `wonValue`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                               p.contracted IS NOT NULL AND
                                ps.`weighting`=1';

        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        $data      = $stmt->fetch();
        $totals[9] = !empty($data['wonValue']) ? $data['wonValue'] : 0;
        // End: Total Won Projects Value


        // Start: Lost Projects Value
        if ( $user_id )
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `lostValue`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.`cancelled` = 1 AND
                                c.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'select  SUM(sy.`ppu` * sy.`quantity`) `lostValue`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.cancelled = 1';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        $totals[10] = !empty($data['lostValue']) ? $data['lostValue'] : 0;
        // End: Lost Projects Value

        // start: value open Projects value
        if ( $user_id )
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `openValue`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            ps.`weighting` = 0 AND
                            ps.`halt`=0 AND
                            c.`user_id`=' . $user_id;
        }
        else
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `openValue`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            ps.`weighting` = 0 AND
                            ps.`halt` = 0';

        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data       = $stmt->fetch();
        $totals[11] = !empty($data['openValue']) ? $data['openValue'] : 0;

        // End: Value open Projects in period

        return $totals;
    }

    /**
     * Get Quarterly Report
     */
    private function getQuarterlyData( \Report\Entity\Report $report, $options = array() )
    {
        $users = array();
        $sql   = 'SELECT * FROM `User` WHERE `active`=1 ORDER BY forename';
        $stmt  = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data       = $stmt->fetchAll();
        $users['0'] = 'All Users';
        // Fetching users to filter reports, this will be displayed in the dropdown box
        if ( !empty($data) )
        {
            foreach ( $data as $item )
            {
                $users[$item['user_id']] = $item['forename'] . ' ' . $item['surname'];
            }
        }

        $tm_to = time();
        $dt_to = date( 'Y-m-d H:i:s', $tm_to );

        $tm_from = mktime( 0, 0, 0, date( 'm', $tm_to ), 1, date( 'Y', $tm_to ) );
        $dt_from = date( 'Y-m-d H:i:s', $tm_from );

        $qtr          = ceil( date( 'm', $tm_to ) / 3 );
        $qtr_start_tm = mktime( 0, 0, 0, (3 * $qtr) - 2, 1, date( 'Y', $tm_to ) );
        $qtr_start_dt = date( 'Y-m-d H:i:s', $qtr_start_tm );
        $qtr_end_tm   = mktime( 0, 0, 0, (3 * $qtr) + 1, 1, date( 'Y', $tm_to ) );
        $qtr_end_dt   = date( 'Y-m-d H:i:s', $qtr_end_tm );

        $yr_start_tm = mktime( 0, 0, 0, 1, 1, date( 'Y', $tm_to ) );
        $yr_start_dt = date( 'Y-m-d H:i:s', $yr_start_tm );
        $yr_end_tm   = mktime( 0, 0, 0, 12, 31, date( 'Y', $tm_to ) );
        $yr_end_dt   = date( 'Y-m-d H:i:s', $yr_end_tm );

        $tm_mthprev_from = mktime( 0, 0, 0, date( 'm', $tm_to ) - 1, 1, date( 'Y', $tm_to ) );
        $dt_mthprev_from = date( 'Y-m-d H:i:s', $tm_mthprev_from );

        $tm_mthprev_to = mktime( 0, 0, 0, date( 'm', $tm_to ), 1, date( 'Y', $tm_to ) );
        $dt_mthprev_to = date( 'Y-m-d H:i:s', $tm_mthprev_to );


        $pivotDate = null;

        $dates = array();
        $stats = array();
        $graph = array(
            array( 'Month', 'New Project', 'Open Project', 'Sold Project' )
        );
        $current_quarter = $this->getCurrentQuarter('current');

        for ( $i = 0; $i <= 3; $i++ )
        {
            $pivotDate = mktime( 0, 0, 0, date( 'm', $current_quarter['start_date'] ) - (3 * $i), 1, date( 'Y' ) );

            $stats[$pivotDate] = array(
                0, // Clients             - Array index : 0
                0, // Contacts            - Array index : 1
                0, // Quotations          - Array index : 2
                0, // Proposals           - Array index : 3
                0, // All Projects  #     - Array index : 4
                0, // won projects  #     - Array index : 5
                0, // lost projects #     - Array index : 6
                0, // open project  #     - Array index : 7
                0, // All Project Value   - Array index : 8
                0, // Won projects Value  - Array index : 9
                0, // Lost projects Value - Array index : 10
                0, // Open projects Value - Array index : 11
            );

            $dates[$pivotDate] = array(
                date( 'M Y', $pivotDate ),
                count( $graph )
            );

            $graph[] = array( date( 'M Y', $pivotDate ), 0, 0, 0 );
        }

        $headings = Array(
            "Clients",
            "Contacts",
            "Quotations",
            "Proposals",
            "All Projects #",
            "Won Projects #",
            "Lost Projects #",
            "Open Projects #",
            "All Project Value",
            "Won Projects Value",
            "Lost Projects Value",
            "Open Project Value"
        );

        // Getting user id, if this is posted to the url,
        // this user id is posted in case of ajax call to filter report data based on user
        $user_id = $this->getRequest()->getPost( 'user_id' );

        // Start: clients Engagements in period
        if ( !empty($user_id) )
        {
            $sql
                = 'SELECT
                       COUNT(*)    `cnt`,
                      QUARTER(`created`)    `qtr`,
                      YEAR(`created`)    `yr`,
                      CONCAT(MONTHNAME(`created`), \'-\' ,YEAR(`created`) , \' \' , MONTHNAME(STR_TO_DATE((MONTH(`created`) + 2), \' %m \') ) ,\'-\', YEAR(`created` ) ) mths_period
                     FROM `Client`
                      WHERE `created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                      `user_id` =  ' . $user_id . '
                    GROUP BY  `yr`, `qtr`
                    ORDER BY yr, qtr, mth DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      COUNT(*)    `cnt`,
                      QUARTER(`created`)    `qtr`,
                      YEAR(`created`)    `yr`,
                      CONCAT(MONTHNAME(`created`), \'-\' ,YEAR(`created`) , \' \' , MONTHNAME(STR_TO_DATE((MONTH(`created`) + 2), \' %m \') ) ,\'-\', YEAR(`created` ) ) mths_period
                     FROM `Client`
                      WHERE `created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                    GROUP BY  `yr`, `qtr`
                    ORDER BY yr, qtr, mths_period DESC';
        }


        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        //echo '<pre>';        print_r($data);
        //exit;
        foreach ( $data as $arr )
        {

            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            //echo $period . '<br/>';

            //echo date('d-M-Y H:i:s', $period);            echo '<br/>';
            if ( isset($dates[$period]) )
            {
                $stats[$period][0] = $arr['cnt'];
            }
        }

        //echo '<pre>';        print_r($dates);        print_r($stats);        exit;
        // End: Clients Engagements in period
        // Start : contacts Engagements in period
        if ( $user_id )
        {
            $sql
                = 'SELECT
                      COUNT(*) `cnt`,
                      QUARTER(c.`created`) `qtr`,
                      YEAR(c.`created`) `yr`
                    FROM `Contact` c
                      INNER JOIN `Client` cl ON cl.`client_id` = c.`client_id`
                    WHERE
                        c.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                        cl.`user_id`=' . $user_id . '
                    GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'SELECT
                      COUNT(*) `cnt`,
                      QUARTER(c.`created`) `qtr`,
                      YEAR(c.`created`) `yr`
                    FROM `Contact` c
                      INNER JOIN `Client` cl ON cl.`client_id` = c.`client_id`
                    WHERE
                        c.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                    GROUP BY `qtr`, `yr`';

        }
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][1] = $arr['cnt'];
            }
        }
        // End: contacts in period

        // Start: Quotations in period
        if ( $user_id )
        {
            $sql
                = 'select COUNT(d.`document_list_id`) `cnt`, QUARTER(d.`created`) `qtr`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` = 5 AND
                                c.`user_id` = ' . $user_id . '
                            GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'select COUNT(d.`document_list_id`) `cnt`, QUARTER(d.`created`) `qtr`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` = 5
                            GROUP BY `qtr`, `yr`';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][2] = $arr['cnt'];
            }
        }
        // End: Quotations in period

        // Start: Proposals Raised in period
        if ( $user_id )
        {
            $sql
                = 'select COUNT(distinct d.`document_list_id`) `cnt`, QUARTER(d.`created`) `qtr`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` IN (1,2,3) AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'select COUNT(distinct d.`document_list_id`) `cnt`, QUARTER(d.`created`) `qtr`, YEAR(d.`created`) `yr`
                            from `DocumentList` d
                            inner join `Project` p ON p.`project_id` = d.`project_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                d.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                d.`document_category_id` IN (1,2,3)
                            GROUP BY `qtr`, `yr`';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][3] = $arr['cnt'];
            }
        }
        // End: Proposals raised in period


        // Start: New projects in period
        if ( $user_id )
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                            GROUP BY `qtr`, `yr`';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][4] = $arr['cnt'];
            }
        }
        // End: new projects in period

        // Start: won Projects in period
        if ( $user_id )
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`contracted`) `qtr`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `qtr`, `yr`, p.project_id';
        }
        else
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`contracted`) `qtr`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1
                            GROUP BY `qtr`, `yr`';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][5] = $arr['cnt'];
            }
        }
        // End: won projects in period

        // Start: lost projects in period
        if ( $user_id )
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                p.`cancelled` = 1 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                p.cancelled = 1
                            GROUP BY `qtr`, `yr`';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][6] = $arr['cnt'];
            }
        }
        // End: lost projects in period

        // Start: Open projects in period
        if ( $user_id )
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting` = 0 AND
                                ps.`halt` = 0 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'select COUNT(*) `cnt`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            where
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting` = 0 AND
                                ps.`halt` = 0
                            GROUP BY `qtr`, `yr`';
        }


        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][7] = $arr['cnt'];
            }
        }
        // End: Open projects in period


        // start: Value All projects in period
        if ( $user_id )
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                            AND c.`user_id`=' . $user_id . '
                        GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                        GROUP BY `qtr`, `yr`';

        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][8] = $arr['price'];
            }
        }
        // End: Value All projects in period


        //Start: Total Won Projects value
        if ( $user_id )
        {
            $sql
                = 'select Sum(sy.ppu * sy.quantity) `price`, QUARTER(p.`contracted`) `qtr`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON p.project_id = sp.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1 AND
                                c.`user_id`=' . $user_id . '
                            GROUP BY `qtr`, `yr`';

        }
        else
        {
            $sql
                = 'SELECT SUM(sy.ppu * sy.quantity) `price`, QUARTER(p.`contracted`) `qtr`, YEAR(p.`contracted`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.`contracted` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                                ps.`weighting`=1
                            GROUP BY `qtr`, `yr`';

        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][9] = $arr['price'];
            }
        }

        //echo '<pre>'; print_r($stats); exit;
        // End: Total Won Projects Value

        // Start: Lost Projects Value
        if ( $user_id )
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                                p.`cancelled` = 1 AND
                                c.`user_id`=' . $user_id . ' AND
                                p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\'
                                GROUP by qtr, yr';
        }
        else
        {
            $sql
                = 'select  SUM(sy.`ppu` * sy.`quantity`) `price`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                            from `Project` p
                            inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            inner join `Client` c ON c.`client_id` = p.`client_id`
                            INNER JOIN `Space` sp ON sp.project_id = p.project_id
                            INNER JOIN `System` sy ON sy.space_id = sp.space_id
                            where
                              p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                              p.cancelled = 1
                              GROUP BY `qtr`, `yr`';
        }
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][10] = $arr['price'];
            }
        }
        // End: Lost Projects Value

        // start: value open Projects in period
        if ( $user_id )
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                            ps.`weighting` = 0 AND
                            ps.`halt`=0 AND
                            c.`user_id`=' . $user_id . '
                        GROUP BY `qtr`, `yr`';
        }
        else
        {
            $sql
                = 'select SUM(sy.`ppu` * sy.`quantity`) `price`, QUARTER(p.`created`) `qtr`, YEAR(p.`created`) `yr`
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        inner join `Project` p ON p.`project_id` = s.`project_id`
                        inner join `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        inner join `Client` c ON c.`client_id` = p.`client_id`
                        where
                            p.`created` >= \'' . date( 'Y-m-d H:i:s', $pivotDate ) . '\' AND
                            ps.`weighting` = 0 AND
                            ps.`halt` = 0
                        GROUP BY `qtr`, `yr`';

        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ( $data as $arr )
        {
            $period = mktime( 0, 0, 0, (3 * $arr['qtr']) - 2, 1, $arr['yr'] );
            if ( isset($dates[$period]) )
            {
                $stats[$period][11] = $arr['price'];
            }
        }
        // End: Value open Projects in period


        $sumArray = array();

        foreach ( $stats as $k => $subArray )
        {
            foreach ( $subArray as $id => $value )
            {
                $sumArray[$id] += $value;
            }
        }

        $stats['total']    = $sumArray;
        $totals            = $this->getAllTotal();
        $stats['allTotal'] = $totals;

        //echo '<pre>';        print_r($stats);        exit;
        return Array( 'stats' => $stats, 'dates' => $dates, 'headings' => $headings, 'users' => $users );
    }

    /**
     * Get sales rating data
     *
     * @param \Report\Entity\Report $report
     * @param array $options
     *
     * @return array
     */
    private function getSalesRatingData( \Report\Entity\Report $report, $options = array() )
    {
        $ratings = Array(
            0 => 'Un-Rated',
            1 => 'Red Rated',
            2 => 'Amber Rated',
            3 => 'Green Rated'
        );
        // Section: All Records
        // Start: Get rating count rating wise for projects
        $result = [ ];
        $sql
                = 'SELECT COUNT(*)  `cnt`, `rating` from `Project`
                    WHERE
                      `Project`.rating IN (0,1,2,3) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0 AND
                      `Project`.cancelled = 0
                    GROUP By `rating`';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }
        // End: rating counts rating wise

        // Start: Total Counts
        $sql
            = 'SELECT COUNT(*)  `cnt` from `Project`
                    WHERE
                      `Project`.rating IN (0,1,2,3) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0  AND
                      `Project`.cancelled = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['cnt'] = $data['cnt'];
        }
        else
        {
            $result['all']['cnt'] = 0;
        }
        // End : Total counts
        // Start: Total Value
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (0,1,2,3) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0  AND
                        p.`cancelled` = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['value'] = $data['value'];
        }
        else
        {
            $result['all']['value'] = 0;
        }

        // End: Total Value

        // start: Rating values
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`, p.`rating` rating
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (0,1,2,3) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0  AND
                        p.`cancelled` = 0
                    GROUP BY rating';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['value'] = $arr['value'];
            }
        }
        // Section End: All records


        // Section Start : User based records

        // Start : Total rating counts user wise
        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                CONCAT(u.`forename`, \' \', u.`surname`) user_name,
                u.user_id
            FROM `Project` p
            INNER JOIN `Client` c
              ON c.`client_id` = p.`client_id`
            RIGHT JOIN `User` u
              ON u.`user_id` = c.`user_id`
            WHERE
              p.`rating` IN (0,1,2,3) AND
              p.`test` = 0 AND
              p.`exclude_from_reporting` = 0  AND
              p.`cancelled` = 0
            GROUP By u.`user_id`
            ORDER BY user_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']]['total_cnt'] = $arr['cnt'];
            }
        }
        // End: Total rating couns user wise

        // Start: Total rating value userwise
        $sql
              = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          client_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  p.created,
                  u.`user_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0  AND
                  p.`cancelled` = 0
                GROUP BY u.user_id
                ORDER BY  user_name';
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']]['total_value'] = $arr['price'];
            }
        }
        // End: Total rating value userwise


        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                CONCAT(u.`forename`, \' \', u.`surname`) user_name, u.user_id
                FROM `Project` p
                INNER JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                RIGHT JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0  AND
                  p.`cancelled` = 0
                GROUP By `rating`, u.`user_id`
                ORDER BY user_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']]['user_name']           = $arr['user_name'];
                $result['user'][$arr['user_id']][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }


        // Project value
        $sql
              = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          client_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  p.created,
                  u.`user_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0   AND
                  p.`cancelled` = 0
                GROUP BY u.user_id, p.rating
                ORDER BY  user_name';
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']][$arr['rating']]['value'] = $arr['price'];
            }
        }

        // Section End : User based records

        return $result;
    }

    /**
     * Get sales rating data
     *
     * @param \Report\Entity\Report $report
     * @param array $options
     *
     * @return array
     */
    private function getSalesRatingByCustomer( \Report\Entity\Report $report, $options = array() )
    {
        $ratings = Array(
            0 => 'Un-Rated',
            1 => 'Red Rated',
            2 => 'Amber Rated',
            3 => 'Green Rated'
        );
        // Section: All Records
        // Start: Get rating count rating wise for projects
        $result = [ ];
        $sql
                = 'SELECT COUNT(*)  `cnt`, `rating` from `Project`
                    WHERE
                      `Project`.rating IN (0,1,2,3) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0 AND
                      `Project`.`cancelled` = 0
                    GROUP By `rating`';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }
        // End: rating counts rating wise

        // Start: Total Counts
        $sql
            = 'SELECT COUNT(*)  `cnt` from `Project`
                    WHERE
                      `Project`.rating IN (0,1,2,3) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0 AND
                      `Project`.`cancelled` = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['cnt'] = $data['cnt'];
        }
        else
        {
            $result['all']['cnt'] = 0;
        }
        // End : Total counts

        // Start: Total Value
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (0,1,2,3) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0 AND
                        p.`cancelled` = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['value'] = $data['value'];
        }
        else
        {
            $result['all']['value'] = 0;
        }

        // End: Total Value

        // start: Rating values
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`, p.`rating` rating
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (0,1,2,3) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0 AND
                        p.`cancelled` = 0
                    GROUP BY rating';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['value'] = $arr['value'];
            }
        }
        // Section End: All records


        // Section Start : User based records

        // Start : Total rating counts user wise
        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                c.name customer_name,
                u.user_id,   c.client_id
            FROM `Project` p
            INNER JOIN `Client` c
              ON c.`client_id` = p.`client_id`
            RIGHT JOIN `User` u
              ON u.`user_id` = c.`user_id`
            WHERE
              p.`rating` IN (0,1,2,3) AND
              p.`test` = 0 AND
              p.`exclude_from_reporting` = 0 AND
              p.`cancelled` = 0
            GROUP By c.`client_id`
            ORDER BY customer_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']]['total_cnt'] = $arr['cnt'];
            }
        }
        // End: Total rating couns user wise

        // Start: Total rating value userwise
        $sql
              = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          customer_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  p.created,
                  u.`user_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
                GROUP BY c.client_id
                ORDER BY  customer_name';
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']]['total_value'] = $arr['price'];
            }
        }
        // End: Total rating value userwise


        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                c.name customer_name, u.user_id, c.client_id
                FROM `Project` p
                INNER JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                RIGHT JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
                GROUP By `rating`, c.`client_id`
                ORDER BY customer_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']]['customer_name']       = $arr['customer_name'];
                $result['customer'][$arr['client_id']][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }


        // Project value
        $sql
              = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          customer_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  p.created,
                  u.`user_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
                GROUP BY c.client_id, p.rating
                ORDER BY  customer_name';
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']][$arr['rating']]['value'] = $arr['price'];
            }
        }

        // Section End : User based records

        return $result;
    }

    /**
     * @param \Report\Entity\Report $report
     * @param array $options
     *
     * @return array
     */
    private function getJobsRatingData( \Report\Entity\Report $report, $options = array() )
    {
        $ratings = Array(
            7 => 'Active',
            8 => 'Suspended',
            9 => 'Completed'
        );
        // Section: All Records
        // Start: Get rating count rating wise for projects
        $result = [ ];
        $sql
                = 'SELECT COUNT(*)  `cnt`, `rating` from `Project`
                    WHERE
                      `Project`.rating IN (7,8,9) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0
                    GROUP By `rating`';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }
        // End: rating counts rating wise

        // Start: Total Counts
        $sql
            = 'SELECT COUNT(*)  `cnt` from `Project`
                    WHERE
                      `Project`.rating IN (7,8,9) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['cnt'] = $data['cnt'];
        }
        else
        {
            $result['all']['cnt'] = 0;
        }
        // End : Total counts

        // Start: Total Value
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (7,8,9) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['value'] = $data['value'];
        }
        else
        {
            $result['all']['value'] = 0;
        }

        // End: Total Value

        // start: Rating values
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`, p.`rating` rating
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (7,8,9) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0
                    GROUP BY rating';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['value'] = $arr['value'];
            }
        }
        // Section End: All records


        // Section Start : User based records

        // Start : Total rating counts user wise
        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                CONCAT(u.`forename`, \' \', u.`surname`) user_name,
                u.user_id
            FROM `Project` p
            INNER JOIN `Client` c
              ON c.`client_id` = p.`client_id`
            RIGHT JOIN `User` u
              ON u.`user_id` = c.`user_id`
            WHERE
              p.`rating` IN (7,8,9) AND
              p.`test` = 0 AND
              p.`exclude_from_reporting` = 0
            GROUP By u.`user_id`
            ORDER BY user_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']]['total_cnt'] = $arr['cnt'];
            }
        }
        // End: Total rating couns user wise

        // Start: Total rating value userwise
        $sql
              = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          client_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  p.created,
                  u.`user_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
                GROUP BY u.user_id
                ORDER BY  user_name';
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']]['total_value'] = $arr['price'];
            }
        }
        // End: Total rating value userwise


        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                CONCAT(u.`forename`, \' \', u.`surname`) user_name, u.user_id
                FROM `Project` p
                INNER JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                RIGHT JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
                GROUP By `rating`, u.`user_id`
                ORDER BY user_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']]['user_name']           = $arr['user_name'];
                $result['user'][$arr['user_id']][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }


        // Project value
        $sql
              = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          client_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  p.created,
                  u.`user_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
                GROUP BY u.user_id, p.rating
                ORDER BY  user_name';
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['user'][$arr['user_id']][$arr['rating']]['value'] = $arr['price'];
            }
        }

        return $result;
    }

    /**
     * @param \Report\Entity\Report $report
     * @param array $options
     *
     * @return array
     */
    private function getJobsRatingByCustomer( \Report\Entity\Report $report, $options = array() )
    {
        $ratings = Array(
            7 => 'Active',
            8 => 'Suspended',
            9 => 'Completed'
        );
        // Section: All Records
        // Start: Get rating count rating wise for projects
        $result = [ ];
        $sql
                = 'SELECT COUNT(*)  `cnt`, `rating` from `Project`
                    WHERE
                      `Project`.rating IN (7,8,9) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0
                    GROUP By `rating`';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }
        // End: rating counts rating wise

        // Start: Total Counts
        $sql
            = 'SELECT COUNT(*)  `cnt` from `Project`
                    WHERE
                      `Project`.rating IN (7,8,9) AND
                      `Project`.test  = 0 AND
                      `Project`.`exclude_from_reporting` = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['cnt'] = $data['cnt'];
        }
        else
        {
            $result['all']['cnt'] = 0;
        }
        // End : Total counts

        // Start: Total Value
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (7,8,9) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetch();

        if ( !empty($data) )
        {
            $result['all']['value'] = $data['value'];
        }
        else
        {
            $result['all']['value'] = 0;
        }

        // End: Total Value

        // start: Rating values
        $sql
            = 'SELECT SUM(sy.`ppu` * sy.`quantity`) `value`, p.`rating` rating
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (7,8,9) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0
                    GROUP BY rating';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['all'][$arr['rating']]['value'] = $arr['value'];
            }
        }
        // Section End: All records


        // Section Start : User based records

        // Start : Total rating counts user wise
        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                c.`name` customer_name,
                u.user_id, c.client_id
            FROM `Project` p
            INNER JOIN `Client` c
              ON c.`client_id` = p.`client_id`
            RIGHT JOIN `User` u
              ON u.`user_id` = c.`user_id`
            WHERE
              p.`rating` IN (7,8,9) AND
              p.`test` = 0 AND
              p.`exclude_from_reporting` = 0
            GROUP By c.`client_id`
            ORDER BY customer_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']]['total_cnt'] = $arr['cnt'];
            }
        }
        // End: Total rating couns user wise

        // Start: Total rating value userwise
        $sql
            = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          customer_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  p.created,
                  u.`user_id`, c.`client_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
                GROUP BY c.client_id
                ORDER BY  customer_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']]['total_value'] = $arr['price'];
            }
        }
        // End: Total rating value userwise


        $sql
            = 'SELECT COUNT(*)  `cnt`, `rating`,
                c.`name` customer_name, u.user_id, c.client_id
                FROM `Project` p
                INNER JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                RIGHT JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
                GROUP By `rating`, c.`client_id`
                ORDER BY customer_name';

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();
        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']]['customer_name']       = $arr['customer_name'];
                $result['customer'][$arr['client_id']][$arr['rating']]['cnt'] = $arr['cnt'];
            }
        }


        // Project value
        $sql
              = 'SELECT
                  p.project_id,
                  p.rating,
                  p.name          project_name,
                  c.name          customer_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity) price,
                  p.created,
                  u.`user_id`
                FROM `Project` p
                  JOIN `Client` c
                    ON c.`client_id` = p.`client_id`
                  JOIN `Space` Sp
                    ON Sp.project_id = p.project_id
                  JOIN `System`	Sy
                    ON Sy.space_id = Sp.space_id
                  JOIN `User` u
                    ON u.`user_id` = c.`user_id`
                WHERE
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
                GROUP BY c.client_id, p.rating
                ORDER BY  customer_name';
        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ( !empty($data) )
        {
            foreach ( $data as $arr )
            {
                $result['customer'][$arr['client_id']][$arr['rating']]['value'] = $arr['price'];
            }
        }
        // Section End : User based records
        //echo '<pre>';        print_r($result);        exit;
        return $result;
    }

    /**
     * @param \Report\Entity\Report $report
     * @param array $options
     */
    private function getOrderBookData( \Report\Entity\Report $report, $options = array() )
    {
        $data['value'] = Array(
            'Year' => Array(
                'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Total'
            )
        );
        $data['count'] = $data['value'];

        $year = date( 'Y' );
        for ( $i = $year; $i >= $year - 3; $i-- )
        {
            $from_date = mktime( 0, 0, 0, 4, 1, $i );
            $to_date   = mktime( 23, 59, 59, 3, 31, $i + 1 );

            $sql
                = 'SELECT 
                      MONTH(p.contracted) mth, 
                      YEAR(p.contracted) yr,
                      SUM(Sy.ppu * Sy.quantity) price
                    FROM Project p
                        JOIN `Client` c
                          ON c.`client_id` = p.`client_id`
                        JOIN `Space` Sp
                          ON Sp.project_id = p.project_id
                        JOIN `System`	Sy
                          ON Sy.space_id = Sp.space_id                  
                    WHERE 
                      p.contracted IS NOT NULL AND
                      p.test = 0 AND 
                      p.exclude_from_reporting = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\')
                    GROUP BY yr, mth
                    ORDER BY yr, mth';

            $result = $this->executeQuery( $sql );

            $data['value'][$i] = Array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

            if ( !empty($result) )
            {
                foreach ( $result as $r )
                {

                    if ( $r['mth'] == 4 )
                    {
                        $data['value'][$i][0] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 5 )
                    {
                        $data['value'][$i][1] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 6 )
                    {
                        $data['value'][$i][2] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 7 )
                    {
                        $data['value'][$i][3] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 8 )
                    {
                        $data['value'][$i][4] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 9 )
                    {
                        $data['value'][$i][5] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 10 )
                    {
                        $data['value'][$i][6] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 11 )
                    {
                        $data['value'][$i][7] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 12 )
                    {
                        $data['value'][$i][8] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 1 )
                    {
                        $data['value'][$i][9] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 2 )
                    {
                        $data['value'][$i][10] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['mth'] == 3 )
                    {
                        $data['value'][$i][11] = '&#163;' . number_format( $r['price'], 0 );
                    }

                    $data['value'][$i][12] += $r['price'];
                }
            }

            $data['count'][$i] = Array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );
            $sql
                               = 'SELECT
                      COUNT(*) cnt, 
                      MONTH(p.contracted) mth, 
                      YEAR(p.contracted) yr
                    FROM Project p
                    WHERE 
                      p.contracted IS NOT NULL AND
                      p.test = 0 AND 
                      p.exclude_from_reporting = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\')
                    GROUP BY yr, mth
                    ORDER BY yr, mth';

            $result = $this->executeQuery( $sql );

            $data['count'][$i] = Array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

            if ( !empty($result) )
            {
                foreach ( $result as $r )
                {
                    if ( $r['mth'] == 4 )
                    {
                        $data['count'][$i][0] = $r['cnt'];
                    }
                    if ( $r['mth'] == 5 )
                    {
                        $data['count'][$i][1] = $r['cnt'];
                    }
                    if ( $r['mth'] == 6 )
                    {
                        $data['count'][$i][2] = $r['cnt'];
                    }
                    if ( $r['mth'] == 7 )
                    {
                        $data['count'][$i][3] = $r['cnt'];
                    }
                    if ( $r['mth'] == 8 )
                    {
                        $data['count'][$i][4] = $r['cnt'];
                    }
                    if ( $r['mth'] == 9 )
                    {
                        $data['count'][$i][5] = $r['cnt'];
                    }
                    if ( $r['mth'] == 10 )
                    {
                        $data['count'][$i][6] = $r['cnt'];
                    }
                    if ( $r['mth'] == 11 )
                    {
                        $data['count'][$i][7] = $r['cnt'];
                    }
                    if ( $r['mth'] == 12 )
                    {
                        $data['count'][$i][8] = $r['cnt'];
                    }
                    if ( $r['mth'] == 1 )
                    {
                        $data['count'][$i][9] = $r['cnt'];
                    }
                    if ( $r['mth'] == 2 )
                    {
                        $data['count'][$i][10] = $r['cnt'];
                    }
                    if ( $r['mth'] == 3 )
                    {
                        $data['count'][$i][11] = $r['cnt'];
                    }

                    $data['count'][$i][12] += $r['cnt'];
                }
            }


        }
        //echo '<pre>'; print_r($data); exit;
        $job               = $this->getEntityManager()->getRepository( '\Report\Entity\Report' );
        $data['chartData'] = $job->getOrderBookChartData();

        return $data;
    }


    /**
     * @param \Report\Entity\Report $report
     * @param array $options
     */
    private function getOrderBookQuarterly( \Report\Entity\Report $report, $options = array() )
    {
        $data['value'] = Array(
            'Year' => Array(
                'Apr-Jun', 'Jul-Sep', 'Oct-Dec', 'Jan-Mar', 'Total'
            )
        );
        $data['count'] = $data['value'];

        $year = date( 'Y' );
        for ( $i = $year; $i >= $year - 3; $i-- )
        {
            $from_date = mktime( 0, 0, 0, 4, 1, $i );
            $to_date   = mktime( 23, 59, 59, 3, 31, $i + 1 );

            $sql
                = 'SELECT
                      YEAR(p.contracted) yr,
                      QUARTER(p.contracted) qtr,
                      SUM(Sy.ppu * Sy.quantity) price
                    FROM Project p
                        JOIN `Client` c
                          ON c.`client_id` = p.`client_id`
                        JOIN `Space` Sp
                          ON Sp.project_id = p.project_id
                        JOIN `System`	Sy
                          ON Sy.space_id = Sp.space_id
                    WHERE
                      p.contracted IS NOT NULL AND
                      p.test = 0 AND
                      p.exclude_from_reporting = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\')
                    GROUP BY yr, qtr
                    ORDER BY yr, qtr';

            $result = $this->executeQuery( $sql );


            $data['value'][$i] = Array( 0, 0, 0, 0, 0 );

            if ( !empty($result) )
            {
                foreach ( $result as $r )
                {

                    if ( $r['qtr'] == 2 )
                    {
                        $data['value'][$i][0] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['qtr'] == 3 )
                    {
                        $data['value'][$i][1] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['qtr'] == 4 )
                    {
                        $data['value'][$i][2] = '&#163;' . number_format( $r['price'], 0 );
                    }
                    if ( $r['qtr'] == 1 )
                    {
                        $data['value'][$i][3] = '&#163;' . number_format( $r['price'], 0 );
                    }

                    $data['value'][$i][4] += $r['price'];
                }
            }

            $sql
                = 'SELECT
                      COUNT(*) cnt,
                      YEAR(p.contracted) yr,
                      QUARTER(p.contracted) qtr
                    FROM Project p
                    WHERE
                      p.contracted IS NOT NULL AND
                      p.test = 0 AND
                      p.exclude_from_reporting = 0 AND
                      (p.project_status_id BETWEEN 40 AND 45) AND
                      (p.contracted BETWEEN \'' . date( 'Y-m-d H:i:s', $from_date ) . '\' AND \'' . date( 'Y-m-d H:i:s', $to_date ) . '\')
                    GROUP BY yr, qtr
                    ORDER BY yr, qtr';

            $result = $this->executeQuery( $sql );

            $data['count'][$i] = Array( 0, 0, 0, 0, 0 );

            if ( !empty($result) )
            {
                foreach ( $result as $r )
                {
                    if ( $r['qtr'] == 2 )
                    {
                        $data['count'][$i][0] = $r['cnt'];
                    }
                    if ( $r['qtr'] == 3 )
                    {
                        $data['count'][$i][1] = $r['cnt'];
                    }
                    if ( $r['qtr'] == 4 )
                    {
                        $data['count'][$i][2] = $r['cnt'];
                    }
                    if ( $r['qtr'] == 1 )
                    {
                        $data['count'][$i][3] = $r['cnt'];
                    }

                    $data['count'][$i][4] += $r['cnt'];
                }
            }
        }

        $job               = $this->getEntityManager()->getRepository( '\Report\Entity\Report' );
        $data['chartData'] = $job->getOrderBookQuarterChartData();

        //echo  '<pre>'; print_r($data); exit;
        return $data;
    }

    /**
     * Creates downloadable csv file.
     *
     * @return \Zend\Mvc\Controller\AbstractController
     * @throws \Exception
     */
    public function downloadAction()
    {
        $group = $this->params()->fromRoute( 'group', false );
        $rid   = $this->params()->fromRoute( 'report', false );

        if ( empty($group) )
        {
            throw new \Exception( 'illegal group route' );
        }

        if ( empty($rid) )
        {
            throw new \Exception( 'illegal report route' );
        }

        $report = $this->getEntityManager()->find( 'Report\Entity\Report', $rid );

        $data = $this->getReportData( $report );

        $filename = strtolower( $report->getName() ) . ' report.csv';
        if ( $report->getReportId() == 6 )
        {
            $export[0][] = 'Month/Year';

            foreach ( $data['headings'] as $val )
            {
                $export[0][] = $val;
            }
            $data['stats']['allTotal'] = $this->getAllTotal();
            foreach ( $data['stats'] as $key => $val )
            {
                if ( $key == 'total' )
                {
                    $export[$key][] = 'Total';
                }
                elseif ( $key == 'allTotal' )
                {
                    $export[$key][] = 'All Total';
                }
                else
                {
                    $export[$key][] = date( 'M Y', $key );
                }

                $export[$key] = array_merge( $export[$key], $val );
            }

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }
        elseif ( $report->getReportId() == 7 )
        {
            $export[] = Array(
                '"Stock Code"',
                '"Bar Code"',
                '"Description"',
                '"Cost Price"',
                '"Sales Price"'
            );

            foreach ( $data as $product )
            {
                $export[] = Array(
                    '"' . $product->getProductId() . '"',
                    '"' . (strlen( $product->getModel() ) > 60 ? substr( $product->getModel(), 0, 56 ) . '...' : $product->getModel()) . '"',
                    '"' . (strlen( $product->getDescription() ) > 60 ? substr( $product->getDescription(), 0, 56 ) . '...' : $product->getDescription()) . '"',
                    '"' . $product->getCpu() . '"',
                    '"' . $product->getPpu() . '"'
                );
            }

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }
        elseif ( $report->getReportId() == 9 )
        {
            $export[] = Array(
                '"User Name"',
                '"RR Count"',
                '"RR Value"',
                '"AR Count"',
                '"AR Value"',
                '"GR Count"',
                '"GR Value"',
                '"UR Count"',
                '"UR Value"',
            );

            foreach ( $data['user'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $value['user_name'] . '"',
                    '"' . (isset($value[1]) ? $value[1]['cnt'] : 0) . '"',
                    '"' . (isset($value[1]) ? number_format( $value[1]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[2]) ? $value[2]['cnt'] : 0) . '"',
                    '"' . (isset($value[2]) ? number_format( $value[2]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[3]) ? $value[3]['cnt'] : 0) . '"',
                    '"' . (isset($value[3]) ? number_format( $value[3]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[0]) ? $value[0]['cnt'] : 0) . '"',
                    '"' . (isset($value[0]) ? number_format( $value[0]['value'], 2 ) : 0) . '"'
                );
            }

            $export[] = Array(
                '"ALL"',
                '"' . ($data['all'][1]['cnt']) . '"',
                '"' . (number_format( $data['all'][1]['value'], 2 )) . '"',
                '"' . ($data['all'][2]['cnt']) . '"',
                '"' . (number_format( $data['all'][2]['value'], 2 )) . '"',
                '"' . ($data['all'][3]['cnt']) . '"',
                '"' . (number_format( $data['all'][3]['value'], 2 )) . '"',
                '"' . ($data['all'][0]['cnt']) . '"',
                '"' . (number_format( $data['all'][0]['value'], 2 )) . '"'
            );

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }
        elseif ( $report->getReportId() == 10 ) // Job rating by owner
        {
            $export[] = Array(
                '"Customer Name"',
                '"Active Count"',
                '"Active Value"',
                '"Suspended Count"',
                '"Suspended Value"',
                '"Completed Count"',
                '"Completed Value"',
            );


            //echo '<pre>'; print_r($data); exit;
            foreach ( $data['user'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $value['user_name'] . '"',
                    '"' . (isset($value[7]) ? $value[7]['cnt'] : 0) . '"',
                    '"' . (isset($value[7]) ? number_format( $value[7]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[8]) ? $value[8]['cnt'] : 0) . '"',
                    '"' . (isset($value[8]) ? number_format( $value[8]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[9]) ? $value[9]['cnt'] : 0) . '"',
                    '"' . (isset($value[9]) ? number_format( $value[9]['value'], 2 ) : 0) . '"',
                );
            }

            $export[] = Array(
                '"ALL"',
                '"' . ($data['all'][7]['cnt']) . '"',
                '"' . (number_format( $data['all'][7]['value'], 2 )) . '"',
                '"' . ($data['all'][8]['cnt']) . '"',
                '"' . (number_format( $data['all'][8]['value'], 2 )) . '"',
                '"' . ($data['all'][9]['cnt']) . '"',
                '"' . (number_format( $data['all'][9]['value'], 2 )) . '"',
            );

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }
        elseif ( $report->getReportId() == 11 )
        {
            $export[] = Array(
                '"Customer Name"',
                '"RR Count"',
                '"RR Value"',
                '"AR Count"',
                '"AR Value"',
                '"GR Count"',
                '"GR Value"',
                '"UR Count"',
                '"UR Value"',
            );

            foreach ( $data['customer'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $value['customer_name'] . '"',
                    '"' . (isset($value[1]) ? $value[1]['cnt'] : 0) . '"',
                    '"' . (isset($value[1]) ? number_format( $value[1]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[2]) ? $value[2]['cnt'] : 0) . '"',
                    '"' . (isset($value[2]) ? number_format( $value[2]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[3]) ? $value[3]['cnt'] : 0) . '"',
                    '"' . (isset($value[3]) ? number_format( $value[3]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[0]) ? $value[0]['cnt'] : 0) . '"',
                    '"' . (isset($value[0]) ? number_format( $value[0]['value'], 2 ) : 0) . '"'
                );
            }

            $export[] = Array(
                '"ALL"',
                '"' . ($data['all'][1]['cnt']) . '"',
                '"' . (number_format( $data['all'][1]['value'], 2 )) . '"',
                '"' . ($data['all'][2]['cnt']) . '"',
                '"' . (number_format( $data['all'][2]['value'], 2 )) . '"',
                '"' . ($data['all'][3]['cnt']) . '"',
                '"' . (number_format( $data['all'][3]['value'], 2 )) . '"',
                '"' . ($data['all'][0]['cnt']) . '"',
                '"' . (number_format( $data['all'][0]['value'], 2 )) . '"'
            );

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }
        elseif ( $report->getReportId() == 12 )
        {
            $export[] = Array(
                '"Customer Name"',
                '"Active Count"',
                '"Active Value"',
                '"Suspended Count"',
                '"Suspended Value"',
                '"Completed Count"',
                '"Completed Value"',
            );

            foreach ( $data['customer'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $value['customer_name'] . '"',
                    '"' . (isset($value[7]) ? $value[7]['cnt'] : 0) . '"',
                    '"' . (isset($value[7]) ? number_format( $value[7]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[8]) ? $value[8]['cnt'] : 0) . '"',
                    '"' . (isset($value[8]) ? number_format( $value[8]['value'], 2 ) : 0) . '"',
                    '"' . (isset($value[9]) ? $value[9]['cnt'] : 0) . '"',
                    '"' . (isset($value[9]) ? number_format( $value[9]['value'], 2 ) : 0) . '"',
                );
            }

            $export[] = Array(
                '"ALL"',
                '"' . ($data['all'][7]['cnt']) . '"',
                '"' . (number_format( $data['all'][7]['value'], 2 )) . '"',
                '"' . ($data['all'][8]['cnt']) . '"',
                '"' . (number_format( $data['all'][8]['value'], 2 )) . '"',
                '"' . ($data['all'][9]['cnt']) . '"',
                '"' . (number_format( $data['all'][9]['value'], 2 )) . '"',
            );

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }
        elseif ( $report->getReportId() == 13 )
        {

            unset($data['chartData'][0], $data['chartData'][1], $data['chartData'][2], $data['chartData'][3]);
            $export[] = Array( 'Qrder Book By Value' );
            $export[] = Array(
                '"Year"',
                '"Apr"',
                '"May"',
                '"Jun"',
                '"Jul"',
                '"Aug"',
                '"Sep"',
                '"Oct"',
                '"Nov"',
                '"Dec"',
                '"Jan"',
                '"Feb"',
                '"Mar"',
                '"Total"',
            );

            foreach ( $data['chartData'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $key . '-' . ($key + 1) . '"',
                    '"' . (number_format( $value[0], 2 )) . '"',
                    '"' . (number_format( $value[1], 2 )) . '"',
                    '"' . (number_format( $value[2], 2 )) . '"',
                    '"' . (number_format( $value[3], 2 )) . '"',
                    '"' . (number_format( $value[4], 2 )) . '"',
                    '"' . (number_format( $value[5], 2 )) . '"',
                    '"' . (number_format( $value[6], 2 )) . '"',
                    '"' . (number_format( $value[7], 2 )) . '"',
                    '"' . (number_format( $value[8], 2 )) . '"',
                    '"' . (number_format( $value[9], 2 )) . '"',
                    '"' . (number_format( $value[10], 2 )) . '"',
                    '"' . (number_format( $value[11], 2 )) . '"',
                    '"' . (number_format( array_sum( $value ), 2 )) . '"',
                );
            }

            $export[] = Array();
            $export[] = Array( 'Qrder Book By Count' );
            $export[] = Array(
                '"Year"',
                '"Apr"',
                '"May"',
                '"Jun"',
                '"Jul"',
                '"Aug"',
                '"Sep"',
                '"Oct"',
                '"Nov"',
                '"Dec"',
                '"Jan"',
                '"Feb"',
                '"Mar"',
                '"Total"',
            );
            unset($data['count']['Year']);
            //echo '<pre>'; print_r($data['count']); exit;
            foreach ( $data['count'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $key . '-' . ($key + 1) . '"',
                    '"' . (number_format( $value[0], 2 )) . '"',
                    '"' . (number_format( $value[1], 2 )) . '"',
                    '"' . (number_format( $value[2], 2 )) . '"',
                    '"' . (number_format( $value[3], 2 )) . '"',
                    '"' . (number_format( $value[4], 2 )) . '"',
                    '"' . (number_format( $value[5], 2 )) . '"',
                    '"' . (number_format( $value[6], 2 )) . '"',
                    '"' . (number_format( $value[7], 2 )) . '"',
                    '"' . (number_format( $value[8], 2 )) . '"',
                    '"' . (number_format( $value[9], 2 )) . '"',
                    '"' . (number_format( $value[10], 2 )) . '"',
                    '"' . (number_format( $value[11], 2 )) . '"',
                    '"' . (number_format( ($value[12]), 2 )) . '"',
                );
            }

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }
        elseif ( $report->getReportId() == 14 )
        {
            unset($data['chartData'][0], $data['chartData'][1], $data['chartData'][2], $data['chartData'][3]);
            $export[] = Array( 'Quarterly Qrder Book By Value' );
            $export[] = Array(
                '"Year"',
                '"Apr-Jul"',
                '"Jul-Sep"',
                '"Oct-Dec"',
                '"Jan-Mar"',
                '"Total"',
            );

            foreach ( $data['chartData'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $key . '-' . ($key + 1) . '"',
                    '"' . (number_format( $value[0], 2 )) . '"',
                    '"' . (number_format( $value[1], 2 )) . '"',
                    '"' . (number_format( $value[2], 2 )) . '"',
                    '"' . (number_format( $value[3], 2 )) . '"',
                    '"' . (number_format( array_sum( $value ), 2 )) . '"',
                );
            }

            $export[] = Array();
            $export[] = Array( 'Quarterly Qrder Book By Count' );
            $export[] = Array(
                '"Year"',
                '"Apr-Jul"',
                '"Jul-Sep"',
                '"Oct-Dec"',
                '"Jan-Mar"',
                '"Total"',
            );
            unset($data['count']['Year']);

            foreach ( $data['count'] as $key => $value )
            {
                $export[] = Array(
                    '"' . $key . '-' . ($key + 1) . '"',
                    '"' . (number_format( $value[0], 2 )) . '"',
                    '"' . (number_format( $value[1], 2 )) . '"',
                    '"' . (number_format( $value[2], 2 )) . '"',
                    '"' . (number_format( $value[3], 2 )) . '"',
                    '"' . (number_format( ($value[4]), 2 )) . '"',
                );
            }

            $response = $this->prepareCSVResponse( $export, $filename );

            return $response;
        }

        $response = $this->prepareCSVResponse( $data, $filename );

        return $response;
    }

    /**
     * Display default view of the report
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function viewAction()
    {
        $group = $this->params()->fromRoute( 'group', false );
        $rid   = $this->params()->fromRoute( 'report', false );
        if ( empty($group) )
        {
            throw new \Exception( 'illegal group route' );
        }

        if ( empty($rid) )
        {
            throw new \Exception( 'illegal report route' );
        }


        $report = $this->getEntityManager()->find( 'Report\Entity\Report', $rid );
        $data   = $this->getReportData( $report );
        $this->getView()
            ->setVariable( 'report', $report )
            ->setVariable( 'partialScript', strtolower( preg_replace( '/[ .-]/i', '', $report->getName() ) ) );

        $this->getView()->setVariable( 'data', $data );

        return $this->getView();
    }

    /**
     * Ajax function to call report data based on user_id,
     * User ID fetched inside the getReportData() function
     *
     * @return JsonModel
     * @throws \Exception
     */
    public function ajaxAction()
    {
        if ( !$this->getRequest()->isXmlHttpRequest() )
        {
            throw new \Exception( 'Direct access to this route is not allowed' );
        }

        $group = $this->params()->fromRoute( 'group', false );
        $rid   = $this->params()->fromRoute( 'report', false );
        if ( empty($group) )
        {
            throw new \Exception( 'Illegal group route' );
        }

        if ( empty($rid) )
        {
            throw new \Exception( 'Illegal report route' );
        }

        $report                    = $this->getEntityManager()->find( 'Report\Entity\Report', $rid );
        $data                      = $this->getReportData( $report );
        $data['stats']['allTotal'] = $this->getAllTotal();

        foreach ( $data['stats'] as $key => $val )
        {
            if ( $key == 'total' )
            {
                $export[$key][] = 'Total';
            }
            elseif ( $key == 'allTotal' )
            {
                $export[$key][] = 'All Total';
            }
            else
            {
                $export[$key][] = date( 'M Y', $key );
            }

            $export[$key] = array_merge( $export[$key], $val );
        }
        $export = array_values( $export );

        $data['stats'] = $export;

        //echo '<pre>'; print_r($export);exit;

        return new JsonModel( $data );
    }

    /**
     * Ajax call to load product data based on product id
     * e.g. if product id submitted is 1000 then all products including 1000 and having product id above 1000 will be
     * returned
     */
    public function ajaxProductAction()
    {
        if ( !$this->getRequest()->isXmlHttpRequest() )
        {
            throw new \Exception( 'Direct access to this route is not allowed' );
        }

        $group = $this->params()->fromRoute( 'group', false );
        $rid   = $this->params()->fromRoute( 'report', false );

        if ( empty($group) )
        {
            throw new \Exception( 'Illegal group route' );
        }

        if ( empty($rid) )
        {
            throw new \Exception( 'Illegal report route' );
        }

        $product_id = $this->params()->fromPost( 'product_id', '' );
        $report     = $this->getEntityManager()->find( 'Report\Entity\Report', $rid );
        $data       = $this->getReportData( $report, array( 'productId' => $product_id ) );

        $jsonResult = array();
        if ( $data )
        {
            foreach ( $data as $product )
            {
                $jsonResult[] = Array(
                    'product_id'  => $product->getProductId(),
                    'model'       => $product->getModel(),
                    'description' => $product->getDescription(),
                    'cpu'         => $product->getCpu(),
                    'ppu'         => $product->getPpu(),
                    'added'       => $product->getCreated()->format( 'd M, Y h:i A' )
                );
            }
        }

        return new JsonModel( $jsonResult );
    }

    /**
     * Download all filtered products
     */
    public function downloadFilteredProductAction()
    {
        $product_id = $this->params()->fromRoute( 'product_id', false );
        $rid        = $this->params()->fromRoute( 'report', false );

        if ( empty($product_id) )
        {
            throw new \Exception( 'Illegal product id route' );
        }

        if ( empty($rid) )
        {
            throw new \Exception( 'Illegal report route' );
        }

        $report   = $this->getEntityManager()->find( 'Report\Entity\Report', $rid );
        $data     = $this->getReportData( $report, array( 'productId' => $product_id ) );
        $filename = strtolower( $report->getName() ) . '_report.csv';
        $export[] = Array(
            '"Stock Code"',
            '"Bar Code"',
            '"Description"',
            '"Cost Price"',
            '"Sales Price"'
        );

        foreach ( $data as $product )
        {
            $export[] = Array(
                '"' . $product->getProductId() . '"',
                '"' . (strlen( $product->getModel() ) > 60 ? substr( $product->getModel(), 0, 56 ) . '...' : $product->getModel()) . '"',
                '"' . (strlen( $product->getDescription() ) > 60 ? substr( $product->getDescription(), 0, 56 ) . '...' : $product->getDescription()) . '"',
                '"' . $product->getCpu() . '"',
                '"' . $product->getPpu() . '"'
            );
        }

        $response = $this->prepareCSVResponse( $export, $filename );

        return $response;
    }

    private function getCurrentQuarter( $quarter_type = '' )
    {
        $current_month = date( 'm' );
        $result        = Array();
        switch ( $quarter_type )
        {
            case 'current':
                if ( $current_month >= 1 && $current_month <= 3 )
                {
                    $result = Array(
                        'start_date' => mktime( 0, 0, 0, 1, 1, date( 'Y' ) ),
                        'end_date'   => mktime( 0, 0, 0, 3, 31, date( 'Y' ) )
                    );
                }
                elseif(  $current_month >= 4 && $current_month <= 6 )
                {
                    $result = Array(
                        'start_date' => mktime( 0, 0, 0, 4, 1, date( 'Y' ) ),
                        'end_date'   => mktime( 0, 0, 0, 6, 30, date( 'Y' ) )
                    );
                }
                elseif ( $current_month >= 7 && $current_month <= 9 )
                {
                    $result = Array(
                        'start_date' => mktime( 0, 0, 0, 7, 1, date( 'Y' ) ),
                        'end_date'   => mktime( 0, 0, 0, 9, 30, date( 'Y' ) )
                    );
                }
                elseif ( $current_month >= 10 && $current_month <= 12 )
                {
                    $result = Array(
                        'start_date' => mktime( 0, 0, 0, 10, 1, date( 'Y' ) ),
                        'end_date'   => mktime( 0, 0, 0, 12, 31, date( 'Y' ) )
                    );
                }
                break;
        }

        return $result;

    }
}
