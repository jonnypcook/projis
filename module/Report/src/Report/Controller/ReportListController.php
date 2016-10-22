<?php
namespace Report\Controller;

use Application\Controller\AuthController;
use Contact\Entity\Mode;
use Zend\View\Model\JsonModel;


class ReportlistController extends AuthController
{

    public function indexAction()
    {
        exit('Here');
    }

    /**
     * Loads reports based on type
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function viewAction()
    {
        $type  = $this->params()->fromRoute( 'type', '' );
        $user  = $this->params()->fromRoute( 'user', '' );
        $month = $this->params()->fromRoute( 'month', '' );
        $year  = $this->params()->fromRoute( 'year', '' );

        if ( empty($type) )
        {
            throw new \Exception( 'Invalid type route' );
        }
        /*
        if ( empty($user) )
        {
            throw new \Exception( 'Invalid user route' );
        }*/

        if ( empty($month) )
        {
            throw new \Exception( 'Invalid month route' );
        }

        if ( empty($year) )
        {
            throw new \Exception( 'Invalid year route' );
        }
        $data = Array();

        $args = Array(
            'user'  => $user,
            'month' => $month,
            'year'  => $year
        );
        switch ( strtolower( $type ) )
        {
            // Start:  Client Engagements
            case 'newclients':
                $this->getView()->setVariable( 'title', 'New Clients' );
                $data = $this->getNewClients( $args );
                break;
            case 'newcontacts':
                $this->getView()->setVariable( 'title', 'New Contacts' );
                $data = $this->getNewContacts( $args );
                break;
            case 'quotations':
                $this->getView()->setVariable( 'title', 'Quotations' );
                $data = $this->getQuotations( $args );
                break;
            case 'newproposals':
                $this->getView()->setVariable( 'title', 'Proposals Raised' );
                $data = $this->getNewProposals( $args );
                break;
            // End: Client Engagements

            // Start: Project Numbers
            case 'allprojects':
                $this->getView()->setVariable( 'title', 'New Projects' );
                $data = $this->getAllProjects( $args );
                break;
            case 'wonproposals':
                $this->getView()->setVariable( 'title', 'Won Projects' );
                $data = $this->getWonProposals( $args );
                break;
            case 'lostproposals':
                $this->getView()->setVariable( 'title', 'Lost Projects' );
                $data = $this->getLostProposals( $args );
                break;
            case 'openprojects':
                $this->getView()->setVariable( 'title', 'Open Projects' );
                $data = $this->getOpenProjects( $args );
                break;
            default:
                throw new \Exception( 'Invalid report type' );
            // End: Project Numbers
        }

        if ( in_array( strtolower( $type ), Array( 'quotations', 'newproposals', 'allprojects', 'wonproposals', 'lostproposals', 'openprojects' ) ) )
        {

            foreach ( $data as $key => $val )
            {
                $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $val['project_id'] );

                $ms      = $this->getServiceLocator()->get( 'Model' );
                $payback = $ms->payback( $project );

                $data[$key]['figures'] = $payback['figures'];

            }
        }

        //echo '<pre>';    print_r($data);   exit;

        $this->getView()
            ->setVariable( 'data', $data )
            ->setVariable( 'month', $month )
            ->setVariable( 'year', $year )
            ->setVariable( 'user_id', $user )
            ->setVariable( 'partialScript', strtolower( $type ) );

        return $this->getView();
    }

    /**
     * Get new clients and returns back to view action
     *
     * @param $args
     *
     * @return mixed
     */
    private function getNewClients( $args )
    {
        if ( !empty($args['user']) && $args['user'] )
        {
            $sql
                = 'SELECT
                      Project.project_id,
                      Project.name          project_name,
                      Client.name           client_name,
                      Client.client_id      client_id,
                      Client.created,
                      SUM(sy.quantity * sy.ppu) price,
                      CONCAT(u.forename , " ", u.surname) user_name
                    FROM `Project`
                      INNER JOIN `Client`
                        ON (`Project`.`client_id` = `Client`.`client_id`)
                      JOIN `Space` sp
                      ON sp.project_id = Project.project_id
                      JOIN `System` sy
                      ON sy.space_id = sp.space_id
                      JOIN `User` u
                      ON u.user_id = `Client`.user_id
                    WHERE (`Client`.user_id=' . $args['user'] . ')
                            AND YEAR(Client.created) = ' . $args['year'] . '
                            AND MONTH(Client.created) = ' . $args['month'] . '
                    GROUP BY client_name
                    Order By Client.created Desc';
        }
        else
        {
            $sql
                = 'SELECT
                      Project.project_id,
                      Project.name          project_name,
                      Client.name           client_name,
                      Client.client_id      client_id,
                      Client.created,
                      SUM(sy.quantity * sy.ppu) price,
                      CONCAT(u.forename, " ", u.surname ) user_name
                    FROM `Project`
                      INNER JOIN `Client`
                        ON (`Project`.`client_id` = `Client`.`client_id`)
                      JOIN `Space` sp
                        ON sp.project_id = Project.project_id
                      JOIN `System` sy
                        ON sy.space_id = sp.space_id
                      JOIN `User` u
                        ON u.user_id = `Client`.user_id
                    WHERE YEAR(Client.created) = ' . $args['year'] . '
                            AND MONTH(Client.created) = ' . $args['month'] . '
                    GROUP BY client_name
                    Order By Client.created desc';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get new contacts, and send results backs to view action for report
     *
     * @param $args
     *
     * @return mixed
     */
    private function getNewContacts( $args )
    {
        if ( !empty($args['user']) )
        {
            $sql
                = 'SELECT
                    c.contact_id, cl.client_id, c.forename, c.surname, cl.name client_name,
                     CONCAT(u.forename, " ", u.surname) user_name,
                     c.created
                FROM
                    `Contact` c
                INNER JOIN
                    `Client` cl
                    ON cl.`client_id` = c.`client_id`
                JOIN `User` u
                   ON u.`user_id` = cl.`user_id`
                WHERE
                    Month(c.`created`) = ' . $args['month']
                . ' AND Year(c.`created`) = ' . $args['year']
                . ' AND cl.`user_id`=' . $args['user'] . '
                group by c.contact_id, cl.client_id
                ORDER BY c.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                    c.contact_id, cl.client_id, c.forename, c.surname, cl.name client_name,
                    CONCAT(u.forename, " ", u.surname) user_name,
                    c.created
                FROM
                    `Contact` c
                INNER JOIN
                    `Client` cl
                    ON cl.`client_id` = c.`client_id`
                JOIN `User` u
                  ON u.`user_id` = cl.`user_id`
                WHERE
                    Month(c.`created`) = ' . $args['month']
                . ' AND Year(c.`created`) = ' . $args['year'] . '
                group by c.contact_id, cl.client_id
                ORDER BY c.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get quotations
     *
     * @param $args
     *
     * @return mixed
     */
    private function getQuotations( $args )
    {
        if ( $args['user'] )
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, c.client_id,
                      CONCAT(u.forename," ", u.surname) user_name,
                      d.document_list_id, SUM(sy.ppu * sy.quantity) price, d.created, s.space_id, SUM(sy.cpu * sy.quantity) cost
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
                        Year(d.`created`) = ' . $args['year'] . ' AND
                        Month(d.`created`) = ' . $args['month'] . ' AND
                        d.`document_category_id` = 5 AND
                        c.`user_id` = ' . $args['user'] . '
                        group by d.document_list_id
                        ORDER BY d.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, c.client_id,
                      CONCAT(u.forename," ", u.surname) user_name,
                      d.document_list_id, SUM(sy.ppu * sy.quantity) price, SUM(sy.cpu * sy.quantity) cost, d.created, s.space_id
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
                        Year(d.`created`) = ' . $args['year'] . ' AND
                        Month(d.`created`) = ' . $args['month'] . ' AND
                        d.`document_category_id` = 5
                        GROUP BY d.document_list_id
                        ORDER BY d.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     *  Get new proposals
     *
     * @param $args
     *
     * @return mixed
     */
    private function getNewProposals( $args )
    {
        if ( $args['user'] )
        {
            $sql
                = 'SELECT
                    p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                    CONCAT(u.forename," ", u.surname) user_name, d.document_list_id, c.client_id, d.created,
                    SUM(sy.cpu * sy.quantity) cost
                        FROM `DocumentList` d
                        LEFT JOIN `Project` p ON p.`project_id` = d.`project_id`
                        LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                        LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                        LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                        LEFT JOIN `User` u
                          ON u.`user_id` = c.`user_id`
                        WHERE
                            Year(d.`created`) = ' . $args['year'] . ' AND
                            Month(d.`created`) = ' . $args['month'] . ' AND
                            d.`document_category_id` IN (1,2,3) AND
                            c.`user_id` = ' . $args['user'] . '
                        GROUP BY d.document_list_id
                        ORDER BY d.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                    p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                    CONCAT(u.forename," ", u.surname) user_name, d.document_list_id, c.client_id, d.created,
                    SUM(sy.cpu * sy.quantity) cost
                        FROM `DocumentList` d
                        LEFT JOIN `Project` p ON p.`project_id` = d.`project_id`
                        LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                        LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                        LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                        LEFT JOIN `User` u ON u.user_id = c.user_id
                        WHERE
                            Year(d.`created`) = ' . $args['year'] . ' AND
                            Month(d.`created`) = ' . $args['month'] . ' AND
                            d.`document_category_id` IN (1,2,3)
                            GROUP BY d.document_list_id
                            ORDER BY d.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get list of all projects
     *
     * @param array $args
     *
     * @return mixed
     */
    private function getAllProjects( $args )
    {
        if ( $args['user'] )
        {
            $sql
                = 'SELECT
                          p.project_id,
                          p.name          project_name,
                          c.name          client_name,
                          c.client_id,
                          SUM(Sy.ppu * Sy.quantity) price,
                          SUM(Sy.cpu * Sy.quantity) cost,
                          CONCAT(u.forename," ", u.surname) user_name,
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
                        WHERE YEAR(p.`created`) = ' . $args['year'] . '
                                AND MONTH(p.`created`) = ' . $args['month'] . '
                                AND c.`user_id`=' . $args['user'] . '
                        GROUP BY p.project_id
                        ORDER BY p.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                          p.project_id,
                          p.name          project_name,
                          c.name          client_name,
                          c.client_id,
                          SUM(Sy.ppu * Sy.quantity) price,
                          SUM(Sy.cpu * Sy.quantity) cost,
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
                        WHERE YEAR(p.`created`) = ' . $args['year'] . '
                            AND MONTH(p.`created`) = ' . $args['month'] . '
                        GROUP BY p.project_id
                        ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get list of won Proposals
     *
     * @param $args
     *
     * @return mixed
     */
    private function getWonProposals( $args )
    {
        if ( $args['user'] )
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                      CONCAT(u.forename, " ", u.surname) user_name, c.client_id, p.created,
                      SUM(sy.cpu * sy.quantity) cost
                    FROM `Project` p
                    LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                    LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                    LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                    LEFT JOIN `User` u ON u.`user_id` = c.`user_id`
                    where
                        Year(p.`contracted`) = ' . $args['year'] . ' AND
                        Month(p.`contracted`) = ' . $args['month'] . ' AND
                        p.`weighting`>=1 AND
                        c.`user_id`=' . $args['user'] . '
                        GROUP BY p.project_id
                        ORDER BY p.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                      CONCAT(u.forename, " ", u.surname ) user_name, c.client_id, p.created,
                      SUM(sy.cpu * sy.quantity) cost
                    FROM `Project` p
                    LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                    LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                    LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                    INNER JOIN `User` u ON u.`user_id` = c.`user_id`
                    where
                        Year(p.`contracted`) = ' . $args['year'] . ' AND
                        Month(p.`contracted`) = ' . $args['month'] . ' AND
                        p.`weighting` >=1
                        GROUP BY p.project_id
                        ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get Lost proposals
     *
     * @param $args
     *
     * @return mixed
     */
    private function getLostProposals( $args )
    {
        if ( $args['user'] )
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                      CONCAT(u.forename, " ", u.surname) user_name, c.client_id, p.created,
                      SUM(sy.cpu * sy.quantity) cost
                            FROM `Project` p
                            LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                            LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                            LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                            LEFT JOIN `User` u ON u.`user_id` = c.`user_id`
                            WHERE
                                Year(p.`created`) = ' . $args['year'] . ' AND
                                Month(p.`created`) = ' . $args['month'] . ' AND
                                p.`cancelled`= 1 AND
                                c.`user_id`=' . $args['user'] . '
                                GROUP BY p.project_id
                                ORDER BY p.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                      CONCAT(u.forename, " ", u.surname) user_name, c.client_id, p.created,
                      SUM(sy.cpu * sy.quantity) cost
                            FROM `Project` p
                            LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                            LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                            LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                            LEFT JOIN `User` u ON u.`user_id` = c.`user_id`
                            WHERE
                                Year(p.`created`) = ' . $args['year'] . ' AND
                                Month(p.`created`) = ' . $args['month'] . ' AND
                                p.`cancelled` = 1
                                GROUP BY p.project_id
                                ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();

    }

    private function getOpenProjects( $args )
    {
        if ( $args['user'] )
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                      CONCAT(u.forename, " " , u.surname) user_name, c.client_id, p.created,
                      SUM(sy.cpu * sy.quantity) cost
                            FROM `Project` p
                            LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                            LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                            LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                            LEFT JOIN `User` u ON u.`user_id` = c.`user_id`
                            WHERE
                                Year(p.`created`) = ' . $args['year'] . ' AND
                                Month(p.`created`) = ' . $args['month'] . ' AND
                                ps.`weighting` = 0 AND
                                ps.`halt` = 0 AND
                                c.`user_id`=' . $args['user'] . '
                                GROUP BY p.project_id
                                ORDER BY p.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p.project_id, p.name project_name, c.name client_name, SUM(sy.ppu * sy.quantity) price,
                      CONCAT(u.forename," ", u.surname) user_name, c.client_id, p.created,
                      SUM(sy.cpu * sy.quantity) cost
                            FROM `Project` p
                            LEFT JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                            LEFT JOIN `Client` c ON c.`client_id` = p.`client_id`
                            LEFT JOIN `Space` sp ON sp.project_id = p.project_id
                            LEFT JOIN `System` sy ON sy.space_id = sp.space_id
                            LEFT JOIN `User` u ON u.`user_id` = c.`user_id`
                            WHERE
                                Year(p.`created`) = ' . $args['year'] . ' AND
                                Month(p.`created`) = ' . $args['month'] . ' AND
                                ps.`weighting`= 0 AND
                                ps.`halt` = 0
                                GROUP BY p.project_id
                                ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
