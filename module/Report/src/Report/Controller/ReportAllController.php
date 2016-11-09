<?php
namespace Report\Controller;

use Application\Controller\AuthController;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;

class ReportAllController extends AuthController
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
        $type = $this->params()->fromRoute( 'type', '' );
        $user = $this->params()->fromRoute( 'user', '' );

        if ( empty($type) )
        {
            throw new \Exception( 'Invalid type route' );
        }

        $data = Array();

        switch ( strtolower( $type ) )
        {
            // Start:  Client Engagements
            case 'clients':
                $this->getView()->setVariable( 'title', 'All Total New Clients' );
                $data = $this->getClients( $user );
                break;
            case 'contacts':
                $this->getView()->setVariable( 'title', 'All Total New Contacts' );
                $data = $this->getContacts( $user );
                break;
            case 'quotations':
                $this->getView()->setVariable( 'title', 'All Total Quotations' );
                $data = $this->getQuotations( $user );
                break;
            case 'proposals':
                $this->getView()->setVariable( 'title', 'All Total Proposals Raised' );
                $data = $this->getProposals( $user );
                break;
            // End: Client Engagements

            // Start: Project Numbers
            case 'allprojects':
                $this->getView()->setVariable( 'title', 'All Total Projects' );
                $data = $this->getAllProjects( $user );
                break;
            case 'wonprojects':
                $this->getView()->setVariable( 'title', 'All Total Won Projects' );
                $data = $this->getWonProjects( $user );
                break;
            case 'lostprojects':
                $this->getView()->setVariable( 'title', 'All Total Lost Projects' );
                $data = $this->getLostProjects( $user );
                break;
            case 'openprojects':
                $this->getView()->setVariable( 'title', 'All Total Open Projects' );
                $data = $this->getOpenProjects( $user );
                break;
            default:
                throw new \Exception( 'Invalid report type' );
            // End: Project Numbers
        }

        $this->getView()
            ->setVariable( 'data', $data )
            ->setVariable( 'user_id', $user )
            ->setVariable( 'partialScript', strtolower( $type ) );

        return $this->getView();
    }

    /**
     * Get all clients
     *
     * @param $user_id
     *
     * @return mixed
     */
    private function getClients( $user_id )
    {

        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );

        if ( !empty($user_id) && $user_id )
        {
            $sql
                = 'SELECT
                      Project.project_id,
                      Project.name          project_name,
                      Client.name           client_name,
                      Client.client_id      client_id,
                      Client.created,
                      SUM(Sy.quantity * Sy.ppu * sp.quantity) price,
                      SUM(Sy.quantity * Sy.cpu * sp.quantity) cost,
                      CONCAT(u.forename , " ", u.surname) user_name
                    FROM `Project`
                      INNER JOIN `Client`
                        ON (`Project`.`client_id` = `Client`.`client_id`)
                      JOIN `Space` sp
                      ON sp.project_id = Project.project_id
                      JOIN `System` Sy
                      ON Sy.space_id = sp.space_id
                      JOIN `User` u
                      ON u.user_id = `Client`.user_id
                    WHERE (`Client`.user_id=' . $user_id . ')
                    GROUP BY client_name
                    ORDER BY Client.created DESC';
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
                      SUM(Sy.quantity * Sy.ppu * sp.quantity) price,
                      CONCAT(u.forename, " ", u.surname ) user_name
                    FROM `Project`
                      INNER JOIN `Client`
                        ON (`Project`.`client_id` = `Client`.`client_id`)
                      JOIN `Space` sp
                        ON sp.project_id = Project.project_id
                      JOIN `System` Sy
                        ON Sy.space_id = sp.space_id
                      JOIN `User` u
                        ON u.user_id = `Client`.user_id
                    GROUP BY client_name
                    ORDER BY Client.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get all contacts, and send results backs to view action for report
     *
     * @param $user_id
     *
     * @return mixed
     */
    private function getContacts( $user_id )
    {
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        if ( !empty($user_id) )
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
                    cl.`user_id` = ' . $user_id . '
                group by c . contact_id, cl . client_id
                ORDER BY c.created DESC';


        }
        else
        {
            $sql
                = 'SELECT
                    c . contact_id, cl . client_id, c . forename, c . surname, cl . name client_name,
                    CONCAT( u . forename, " ", u . surname ) user_name,
                    c . created
                FROM
                    `Contact` c
                INNER JOIN
                    `Client` cl
                    ON cl . `client_id` = c . `client_id`
                JOIN `User` u
                  ON u . `user_id` = cl . `user_id`
                group by c . contact_id, cl . client_id
                ORDER BY c.created DESC';
        }


        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();


        return $stmt->fetchAll();
    }

    /**
     * Get all quotations
     *
     * @param $args
     *
     * @return mixed
     */
    private function getQuotations( $user_id )
    {
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        if ( !empty($user_id) )
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, c . client_id,
                      CONCAT( u . forename, " ", u . surname ) user_name,
                      d . document_list_id, SUM( Sy . ppu * Sy . quantity * s.quantity) price, d . created
                    FROM `DocumentList` d
                        INNER JOIN `Project` p
                          ON p . `project_id` = d . `project_id`
                        INNER JOIN `Client` c
                          ON c . `client_id` = p . `client_id`
                        INNER JOIN `User` u
                          ON u . `user_id` = c . `user_id`
                        INNER JOIN `Space` s
                          ON s . `project_id` = p . `project_id`
                        INNER JOIN `System` Sy
                          ON Sy . space_id = s . space_id

                    WHERE
                        d . `document_category_id` = 5 AND
                        c . `user_id` = ' . $user_id . '
                    group by d.document_list_id
                    ORDER BY d.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, c . client_id,
                      CONCAT( u . forename, " ", u . surname ) user_name,
                      d . document_list_id, SUM( Sy . ppu * Sy . quantity * s.quantity ) price, d . created
                    FROM `DocumentList` d
                        INNER JOIN `Project` p
                          ON p . `project_id` = d . `project_id`
                        INNER JOIN `Client` c
                          ON c . `client_id` = p . `client_id`
                        INNER JOIN `User` u
                          ON u . `user_id` = c . `user_id`
                        INNER JOIN `Space` s
                          ON s . `project_id` = p . `project_id`
                        INNER JOIN `System` Sy
                          ON Sy . space_id = s . space_id
                    WHERE
                        d . `document_category_id` = 5
                        GROUP BY d . document_list_id
                        ORDER BY d.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     *  Get all proposals
     *
     * @param $user_id
     *
     * @return mixed
     */
    private function getProposals( $user_id )
    {
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        if ( $user_id )
        {
            $sql
                = 'SELECT
                    p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                    CONCAT( u . forename, " ", u . surname ) user_name, d . document_list_id, c . client_id, d . created
                        FROM `DocumentList` d
                        INNER JOIN `Project` p ON p . `project_id` = d . `project_id`
                        INNER JOIN `Client` c ON c . `client_id` = p . `client_id`
                        INNER JOIN `Space` sp ON sp . project_id = p . project_id
                        INNER JOIN `System` Sy ON Sy . space_id = sp . space_id
                        INNER JOIN `User` u
                          ON u . `user_id` = c . `user_id`
                        WHERE
                            d . `document_category_id` IN( 1, 2, 3 ) AND
                            c . `user_id` = ' . $user_id . '
                        GROUP BY d.document_list_id
                        ORDER BY d.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                    p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                    CONCAT( u . forename, " ", u . surname ) user_name, d . document_list_id, c . client_id, d . created
                FROM `DocumentList` d
                    INNER JOIN `Project` p ON p . `project_id` = d . `project_id`
                    INNER JOIN `Client` c ON c . `client_id` = p . `client_id`
                    INNER JOIN `Space` sp ON sp . project_id = p . project_id
                    INNER JOIN `System` Sy ON Sy . space_id = sp . space_id
                    INNER JOIN `User` u ON u . user_id = c . user_id
                WHERE
                      d . `document_category_id` IN( 1, 2, 3 )
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
     * @param integer $user_id
     *
     * @return mixed
     */
    private function getAllProjects( $user_id )
    {
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        if ( $user_id )
        {
            $sql
                = 'SELECT
                          p . project_id,
                          p . name          project_name,
                          c . name          client_name,
                          c . client_id,
                          SUM( Sy.ppu * Sy.quantity * Sp.quantity ) price,
                          SUM(Sy.quantity * Sy.cpu * Sp.quantity) cost,
                          CONCAT( u.forename, " ", u . surname ) user_name,
                          p . created
                        FROM `Project` p
                          JOIN `Client` c
                            ON c.`client_id` = p . `client_id`
                          JOIN `Space` Sp
                            ON Sp.project_id = p . project_id
                          JOIN `System`	Sy
                            ON Sy.space_id = Sp . space_id
                          JOIN `User` u
                            ON u.`user_id` = c . `user_id`
                        WHERE
                             c . `user_id` = ' . $user_id . '
                        GROUP BY p.project_id
                        ORDER BY p.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                          p . project_id,
                          p . name          project_name,
                          c . name          client_name,
                          c . client_id,
                          SUM( Sy . ppu * Sy . quantity * Sp.quantity ) price,
                          SUM(Sy.quantity * Sy.cpu * Sp.quantity) cost,
                          CONCAT( u.forename, " ", u.surname ) user_name,
                          p . created
                        FROM `Project` p
                          JOIN `Client` c
                            ON c . `client_id` = p . `client_id`
                          JOIN `Space` Sp
                            ON Sp.project_id = p . project_id
                          JOIN `System`	Sy
                            ON Sy.space_id = Sp . space_id
                          JOIN `User` u
                            ON u.`user_id` = c . `user_id`
                        GROUP BY p.project_id
                        ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get list of all won Projects
     *
     * @param $user_id
     *
     * @return mixed
     */
    private function getWonProjects( $user_id )
    {
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        if ( $user_id )
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                      SUM(Sy.quantity * Sy.cpu * sp.quantity) cost,  CONCAT( u . forename, " ", u . surname ) user_name,
                      c . client_id, p . created
                    FROM `Project` p
                    INNER JOIN `Project_Status` ps ON ps . `project_status_id` = p . `project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    INNER JOIN `Space` sp ON sp.project_id = p.project_id
                    INNER JOIN `System` Sy ON Sy.space_id = sp.space_id
                    INNER JOIN `User` u ON u . `user_id` = c . `user_id`
                    where
                        ps. `weighting` = 1 AND
                        p.contracted IS NOT NULL AND
                        c . `user_id` = ' . $user_id . '
                    GROUP BY p.project_id
                    ORDER BY p.contracted DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                      SUM(Sy.quantity * Sy.cpu * sp.quantity) cost, CONCAT( u . forename, " ", u . surname ) user_name,
                      c . client_id, p . created
                    FROM `Project` p
                        LEFT JOIN `Project_Status` ps ON ps . `project_status_id` = p . `project_status_id`
                        LEFT JOIN `Client` c ON c . `client_id` = p . `client_id`
                        LEFT JOIN `Space` sp ON sp . project_id = p . project_id
                        LEFT JOIN `System` Sy ON Sy . space_id = sp . space_id
                        INNER JOIN `User` u ON u . `user_id` = c.`user_id`
                    where
                        p.contracted IS NOT NULL AND
                        ps.`weighting` = 1
                    GROUP BY p.project_id
                    ORDER BY p.contracted DESC';
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
    private function getLostProjects( $user_id )
    {
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        if ( $user_id )
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                      SUM(Sy.quantity * Sy.cpu * sp.quantity) cost, CONCAT( u . forename, " ", u . surname ) user_name,
                      c . client_id, p . created
                            FROM `Project` p
                            INNER JOIN `Project_Status` ps ON ps . `project_status_id` = p . `project_status_id`
                            INNER JOIN `Client` c ON c . `client_id` = p . `client_id`
                            INNER JOIN `Space` sp ON sp . project_id = p . project_id
                            INNER JOIN `System` Sy ON Sy . space_id = sp . space_id
                            INNER JOIN `User` u ON u . `user_id` = c . `user_id`
                            WHERE
                                p . `cancelled` = 1 AND
                                c . `user_id` = ' . $user_id . '
                                month(
                                GROUP BY p . project_id
                                ORDER BY p.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                      SUM(Sy.quantity * Sy.cpu * sp.quantity) cost, CONCAT( u . forename, " ", u . surname ) user_name,
                      c . client_id, p . created
                            FROM `Project` p
                            INNER JOIN `Project_Status` ps ON ps . `project_status_id` = p . `project_status_id`
                            INNER JOIN `Client` c ON c . `client_id` = p . `client_id`
                            INNER JOIN `Space` sp ON sp . project_id = p . project_id
                            INNER JOIN `System` Sy ON Sy . space_id = sp . space_id
                            INNER JOIN `User` u ON u . `user_id` = c . `user_id`
                            WHERE
                                p . `cancelled` = 1
                            GROUP BY p . project_id
                            ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();

    }

    private function getOpenProjects( $user_id )
    {
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        if ( $user_id )
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                      SUM(Sy.quantity * Sy.cpu * sp.quantity) cost, CONCAT( u . forename, " ", u . surname ) user_name,
                      c . client_id, p . created
                            FROM `Project` p
                            INNER JOIN `Project_Status` ps ON ps . `project_status_id` = p . `project_status_id`
                            INNER JOIN `Client` c ON c . `client_id` = p . `client_id`
                            INNER JOIN `Space` sp ON sp . project_id = p . project_id
                            INNER JOIN `System` Sy ON Sy . space_id = sp . space_id
                            INNER JOIN `User` u ON u . `user_id` = c . `user_id`
                            WHERE
                                ps . `weighting` = 0 AND
                                ps . `halt` = 0 AND
                                c . `user_id` = ' . $user_id . '
                                GROUP BY p . project_id
                                ORDER BY p.created DESC';
        }
        else
        {
            $sql
                = 'SELECT
                      p . project_id, p . name project_name, c . name client_name, SUM( Sy . ppu * Sy . quantity * sp.quantity ) price,
                      SUM(Sy.quantity * Sy.cpu * sp.quantity) cost, CONCAT( u . forename, " ", u . surname ) user_name,
                      c . client_id, p . created
                            FROM `Project` p
                                INNER JOIN `Project_Status` ps ON ps . `project_status_id` = p . `project_status_id`
                                INNER JOIN `Client` c ON c . `client_id` = p . `client_id`
                                INNER JOIN `Space` sp ON sp . project_id = p . project_id
                                INNER JOIN `System` Sy ON Sy . space_id = sp . space_id
                                INNER JOIN `User` u ON u . `user_id` = c . `user_id`
                            WHERE
                                ps . `weighting` = 0 AND
                                ps . `halt` = 0
                            GROUP BY p . project_id
                            ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Export total reports
     */
    public function exportTotalAction()
    {
        $type = $this->params()->fromRoute( 'type', '' );
        $user = $this->params()->fromRoute( 'user', '' );

        switch ( $type )
        {
            case 'allprojects':
                $System = $this->getAllProjects( $user );
                break;
            case 'wonprojects':
                $System = $this->getWonProjects( $user );
                break;
            case 'lostprojects':
                $System = $this->getLostProjects( $user );
                break;
            case 'openprojects':
                $System = $this->getOpenProjects( $user );
                break;
        }
        $data[] = array(
            '"Reference"',
            '"Project"',
            '"Client"',
            '"Project Value"',
            //'"Margin"',
            '"Owner"',
            '"Date"'
        );

        if ( !empty($System) )
        {
            $total_value = 0;
            foreach ( $System as $item )
            {

                $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $item['project_id'] );
                //$ms      = $this->getServiceLocator()->get( 'Model' );
                //$payback = $ms->payback( $project );
                //$figures = $payback['figures'];

                //$tmpPpuTotal = $figures['cost'];
                //$tmpCpuTotal = $item['cost'];
                //$prjMargin   = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                //echo number_format($prjMargin,2) . " %";

                $data[] = array(
                    str_pad( $item['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $item['project_id'], 5, "0", STR_PAD_LEFT ),
                    '"' . $item['project_name'] . '"',
                    '"' . $item['client_name'] . '"',
                    '"' . number_format( $item['price'], 2 ) . '"',
                   // '"' . number_format( $prjMargin, 2 ) . '%' . '"',
                    '"' . $item['user_name'] . '"',
                    '"' . date( 'd M, Y', strtotime( $item['created'] ) ) . '"'
                );

                $total_value += $item['price'];
            }

            $data[] = array(
                '',
                '',
                'Total Value ',
                '"' . number_format( $total_value, 2 ) . '"'
            );

        }
        $dt = mktime( 0, 0, 0, date( 'm' ) - 11, 1, date( 'Y' ) );
        $filename = ucfirst( $type ) . '_List_from_' . date( 'd-M-Y', $dt ) . '.csv';

        $response = $this->prepareCSVResponse( $data, $filename );

        return $response;
    }

    /**
     * Export all projects to csv
     *
     * @param $args
     */
    private function exportAllTotalProjects( $user )
    {
        $data[] = array(
            '"Reference"',
            '"Project"',
            '"Client"',
            '"Project Value"',
            '"Margin"',
            '"Owner"',
            '"Date"'
        );

        $System = $this->getAllProjects( $user );
        if ( !empty($System) )
        {
            $total_value = 0;
            foreach ( $System as $item )
            {

                $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $item['project_id'] );
                $ms      = $this->getServiceLocator()->get( 'Model' );
                $payback = $ms->payback( $project );
                $figures = $payback['figures'];

                $tmpPpuTotal = $figures['cost'];
                $tmpCpuTotal = $item['cost'];
                $prjMargin   = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                //echo number_format($prjMargin,2) . " %";

                $data[] = array(
                    str_pad( $item['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $item['project_id'], 5, "0", STR_PAD_LEFT ),
                    '"' . $item['project_name'] . '"',
                    '"' . $item['client_name'] . '"',
                    '"' . number_format( $figures['cost'], 2 ) . '"',
                    '"' . number_format( $prjMargin, 2 ) . '%' . '"',
                    '"' . $item['user_name'] . '"',
                    '"' . date( 'd M, Y', strtotime( $item['created'] ) ) . '"'
                );

                $total_value += $figures['cost'];
            }

            $data[] = array(
                '',
                '',
                'Total Value ',
                '"' . number_format( $total_value, 2 ) . '"'
            );

        }

        $filename = 'All_Projects_List_from' . date( 'Y-m-d', $dt ) . '.csv';

        $response = $this->prepareCSVResponse( $data, $filename );

        return $response;
    }
}
