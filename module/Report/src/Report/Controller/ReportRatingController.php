<?php
namespace Report\Controller;

use Application\Controller\AuthController;
use Contact\Entity\Mode;
use Doctrine\Common\Annotations\AnnotationException;
use Zend\View\Model\JsonModel;


class ReportRatingController extends AuthController
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
        $rating = $this->params()->fromRoute( 'rating', '' );
        $user   = $this->params()->fromRoute( 'user', '' ); // User for owner, client for customer
        if ( empty($rating) )
        {
            throw new \Exception( 'Invalid rating route' );
        }
        $data = Array();

        switch ( strtolower( $rating ) )
        {
            case 'ur': // Un-Rated
                $this->getView()->setVariable( 'title', 'Un-Rated - Ownerwise' );
                $this->setCaption( 'Reporting : Un-Rated - Ownerwise' );
                $data = $this->getData( 0, $user );
                $this->getView()->setVariable( 'partialScript', 'viewlist' );
                break;
            case 'rr': // Red Rated
                $this->getView()->setVariable( 'title', 'Red Rated - Ownerwise' );
                $this->setCaption( 'Reporting : Red Rated - Ownerwise' );
                $data = $this->getData( 1, $user );
                $this->getView()->setVariable( 'partialScript', 'viewlist' );
                break;
            case 'ar': // Amber Rated
                $this->getView()->setVariable( 'title', 'Amber Rated - Ownerwise' );
                $this->setCaption( 'Reporting :  Amber Rated - Ownerwise' );
                $data = $this->getData( 2, $user );
                $this->getView()->setVariable( 'partialScript', 'viewlist' );
                break;
            case 'gr': // Green Rated
                $this->getView()->setVariable( 'title', 'Green Rated - Ownerwise' );
                $this->setCaption( 'Reporting : Green Rated - Ownerwise' );
                $data = $this->getData( 3, $user );
                $this->getView()->setVariable( 'partialScript', 'viewlist' );
                break;
            case 'all': // All ratings
                $this->setCaption( 'Reporting : All Rating - Ownerwise' );
                $this->getView()->setVariable( 'title', 'All Ratings - Ownerwise' );
                $data = $this->getAllRatingsData( $user );
                $this->getView()->setVariable( 'partialScript', 'list' );
                break;
            case 'cur': // Un-Rated customer wise
                $this->getView()->setVariable( 'title', 'Un-Rated - Customerwise' );
                $this->setCaption( 'Reporting : Un-Rated - Customerwise' );
                $data = $this->getCustomerData( 0, $user );
                $this->getView()->setVariable( 'partialScript', 'customerSales' );
                break;
            case 'crr': // Red Rated customer wise
                $this->getView()->setVariable( 'title', 'Red Rated - Customerwise' );
                $this->setCaption( 'Reporting : Red Rated - Customerwise' );
                $data = $this->getCustomerData( 1, $user );
                $this->getView()->setVariable( 'partialScript', 'customerSales' );
                break;
            case 'car': // Amber Rated customer wise
                $this->getView()->setVariable( 'title', 'Amber Rated - Customerwise' );
                $this->setCaption( 'Reporting : Amber Rated - Customerwise' );
                $data = $this->getCustomerData( 2, $user );
                $this->getView()->setVariable( 'partialScript', 'customerSales' );
                break;
            case 'cgr': // Green Rated customer wise
                $this->getView()->setVariable( 'title', 'Green Rated - Customerwise' );
                $this->setCaption( 'Reporting : Green Rated - Customerwise' );
                $data = $this->getCustomerData( 3, $user );
                $this->getView()->setVariable( 'partialScript', 'customerSales' );
                break;
            case 'call': // All ratings customer wise
                $this->getView()->setVariable( 'title', 'All Ratings - Customerwise' );
                $this->setCaption( 'Reporting : All Ratings - Customerwise' );
                $data = $this->getAllCustomerSalesData( $user );
                $this->getView()->setVariable( 'partialScript', 'customerSales' );
                break;
            case 'active':
                $this->getView()->setVariable( 'title', 'Active Jobs - Ownerwise' );
                $this->setCaption( 'Reporting : Active Jobs - Ownerwise' );
                $data = $this->getData( 7, $user );
                $this->getView()->setVariable( 'partialScript', 'jobs' );
                break;
            case 'suspended':
                $this->getView()->setVariable( 'title', 'Suspended Jobs - Ownerwise' );
                $this->setCaption( 'Reporting : Suspended Jobs - Ownerwise' );
                $data = $this->getData( 8, $user );
                $this->getView()->setVariable( 'partialScript', 'jobs' );
                break;
            case 'completed':
                $this->getView()->setVariable( 'title', 'Completed Jobs - Ownerwise' );
                $this->setCaption( 'Reporting : Completed Jobs - Ownerwise' );
                $data = $this->getData( 9, $user );
                $this->getView()->setVariable( 'partialScript', 'jobs' );
                break;
            case 'jobs':
                $this->getView()->setVariable( 'title', 'All Jobs - Ownerwise' );
                $this->setCaption( 'Reporting : All Jobs - Ownerwise' );
                $data = $this->getAllJobsData( $user );
                $this->getView()->setVariable( 'partialScript', 'jobs' );
                break;
            case 'cactive': // Customer Active
                $this->getView()->setVariable( 'title', 'Active Jobs - Customerwise' );
                $this->setCaption( 'Reporting : Active Jobs - Customerwise' );
                $data = $this->getCustomerData( 7, $user );
                $this->getView()->setVariable( 'partialScript', 'customerJobs' );
                break;
            case 'csuspended': // Customer Suspended
                $this->getView()->setVariable( 'title', 'Suspended Jobs - Customerwise' );
                $this->setCaption( 'Reporting : Suspended Jobs - Customerwise' );
                $data = $this->getCustomerData( 8, $user );
                $this->getView()->setVariable( 'partialScript', 'customerJobs' );
                break;
            case 'ccompleted': // Customer completed
                $this->getView()->setVariable( 'title', 'Completed Jobs - Customerwise' );
                $this->setCaption( 'Reporting : Completed Jobs - Customerwise' );
                $data = $this->getCustomerData( 9, $user );
                $this->getView()->setVariable( 'partialScript', 'customerJobs' );
                break;
            case 'cjobs': // Customer Jobs
                $this->getView()->setVariable( 'title', 'All  Jobs - Customerwise' );
                $this->setCaption( 'Reporting : All  Jobs - Customerwise' );
                $data = $this->getAllJobsCustomerData( $user );
                $this->getView()->setVariable( 'partialScript', 'customerJobs' );
                break;
            default:
                throw new \Exception( 'Invalid report type' );
        }

        $u = $this->getEntityManager()->getRepository( '\Application\Entity\User' )->find( $user );

        $this->getView()
            ->setVariable( 'data', $data )
            ->setVariable( 'rating', $rating )
            ->setVariable( 'user_id', $user )
            ->setVariable( 'user', $u );

        return $this->getView();
    }

    /**
     * Export report
     */
    public function exportAction()
    {
        $rating = $this->params()->fromRoute( 'rating', '' );
        $user   = $this->params()->fromRoute( 'user', '' );
        if ( empty($rating) )
        {
            throw new \Exception( 'Invalid rating route' );
        }

        $data   = Array();
        $data[] = array(
            '"Reference"',
            '"Project"',
            '"Client"',
            '"Project Value"',
            '"Owner"'
        );

        switch ( strtolower( $rating ) )
        {
            case 'ur': // Un-Rated
                $this->getView()->setVariable( 'title', 'Un-Rated' );
                $system = $this->getData( 0, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'rr': // Red Rated
                $this->getView()->setVariable( 'title', 'Red Rated' );
                $system = $this->getData( 1, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'ar': // Amber Rated
                $this->getView()->setVariable( 'title', 'Amber Rated' );
                $system = $this->getData( 2, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'gr': // Green Rated
                $this->getView()->setVariable( 'title', 'Green Rated' );
                $system = $this->getData( 3, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'all': // All ratings
                $this->getView()->setVariable( 'title', 'All Ratings' );
                $system = $this->getAllRatingsData( $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'cur': // Un-Rated customer wise
                $this->getView()->setVariable( 'title', 'Un-Rated' );
                $system = $this->getCustomerData( 0, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'crr': // Red Rated customer wise
                $this->getView()->setVariable( 'title', 'Red Rated' );
                $system = $this->getCustomerData( 1, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'car': // Amber Rated customer wise
                $this->getView()->setVariable( 'title', 'Amber Rated' );
                $system = $this->getCustomerData( 2, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'cgr': // Green Rated customer wise
                $this->getView()->setVariable( 'title', 'Green Rated' );
                $system = $this->getCustomerData( 3, $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'call': // All ratings customer wise
                $this->getView()->setVariable( 'title', 'All Ratings' );
                $system = $this->getAllCustomerSalesData( $user );
                array_push( $data[0], "\"Added\"", "\"Expected\"" );
                break;
            case 'active':
                $this->getView()->setVariable( 'title', 'Active' );
                $system = $this->getData( 7, $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            case 'suspended':
                $this->getView()->setVariable( 'title', 'Suspended' );
                $system = $this->getData( 8, $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            case 'completed':
                $this->getView()->setVariable( 'title', 'Completed' );
                $system = $this->getData( 9, $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            case 'jobs':
                $this->getView()->setVariable( 'title', 'All Jobs' );
                $system = $this->getAllJobsData( $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            case 'cactive': // Customer Active
                $this->getView()->setVariable( 'title', 'Active' );
                $system = $this->getCustomerData( 7, $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            case 'csuspended': // Customer Suspended
                $this->getView()->setVariable( 'title', 'Suspended' );
                $system = $this->getCustomerData( 8, $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            case 'ccompleted': // Customer completed
                $this->getView()->setVariable( 'title', 'Completed' );
                $system = $this->getCustomerData( 9, $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            case 'cjobs': // Customer Jobs
                $this->getView()->setVariable( 'title', 'All customer' );
                $system = $this->getAllJobsCustomerData( $user );
                array_push( $data[0], "\"Started\"", "\"Completed\"" );
                break;
            default:
                throw new \Exception( 'Invalid report type' );
        }
        if ( !empty($system) )
        {
            $total_value = 0;
            foreach ( $system as $item )
            {
                /*
                $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $item['project_id'] );
                $ms      = $this->getServiceLocator()->get( 'Model' );
                $payback = $ms->payback( $project );
                $figures = $payback['figures'];

                $tmpPpuTotal = $figures['cost'];
                $tmpCpuTotal = $item['cost'];
                $prjMargin   = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                //echo number_format($prjMargin,2) . " %";
                */
                if ( in_array( strtolower( $rating ), Array( 'ur', 'ar', 'gr', 'rr', 'all', 'cur', 'car', 'cgr', 'crr', 'call' ) ) )
                {
                    $data[] = array(
                        str_pad( $item['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $item['project_id'], 5, "0", STR_PAD_LEFT ),
                        '"' . $item['project_name'] . '"',
                        '"' . $item['client_name'] . '"',
                        '"' . number_format( $item['price'], 2 ) . '"',
                        // '"' . number_format( $prjMargin, 2 ) . '%' . '"',
                        '"' . $item['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $item['created'] ) ) . '"',
                        '"' . (!empty($item['expected_date']) ? date( 'd M, Y', strtotime( $item['expected_date'] ) ) : "") . '"'
                    );
                }
                else
                {
                    $data[] = array(
                        str_pad( $item['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $item['project_id'], 5, "0", STR_PAD_LEFT ),
                        '"' . $item['project_name'] . '"',
                        '"' . $item['client_name'] . '"',
                        '"' . number_format( $item['price'], 2 ) . '"',
                        // '"' . number_format( $prjMargin, 2 ) . '%' . '"',
                        '"' . $item['user_name'] . '"',
                        '"' . (!empty($item['contracted']) ? date( 'd M, Y', strtotime( $item['contracted'] ) ) : "") . '"',
                        '"' . (!empty($item['completed']) ? date( 'd M, Y', strtotime( $item['completed'] ) ) : "") . '"'
                    );

                }
                $total_value += $item['price'];
            }

            $data[] = array(
                '',
                '',
                'Total Value ',
                '"' . number_format( $total_value, 2 ) . '"'
            );
        }

        $arr = Array(
            'ur'         => 'Un-Rated Sales Rating - Ownerwise',
            'rr'         => 'Red Rated Sales Rating - Ownerwise',
            'ar'         => 'Amber Rated Sales Rating - Ownerwise',
            'gr'         => 'Green Rated Sales Rating - Ownerwise',
            'all'        => 'All Sales Rating - Ownerwise',
            'cur'        => 'Un-rated Sales Rating - Customerwise',
            'crr'        => 'Red Rated Sales Rating - Customerwise',
            'car'        => 'Amber Rated Sales Rating - Customerwise',
            'cgr'        => 'Green Rated Sales Rating - Customerwise',
            'call'       => 'All Sales Rating - Customerwise',
            'active'     => 'Active Jobs - Ownerwise',
            'suspended'  => 'Suspended Jobs - Ownerwise',
            'completed'  => 'Completed Jobs - Ownerwise',
            'jobs'       => 'All Jobs - Ownerwise',
            'cactive'    => 'Active Jobs - Customerwise',
            'csuspended' => 'Suspended Jobs - Customerwise',
            'ccompleted' => 'Completed Jobs - Customerwise',
            'cjobs'      => 'All Jobs - Customerwise',
        );

        $filename = (in_array( $rating, array_keys( $arr ) ) ? $arr[$rating] : '') . ' - Report.csv';
        $response = $this->prepareCSVResponse( $data, $filename );

        return $response;
    }

    /**
     * Get Un Rated projects
     *
     * @param $args
     *
     * @return mixed
     */
    private function getData( $rating, $user = '' )
    {
        if ( $user )
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
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` = \'' . $rating . '\' AND
                  c.user_id = \'' . $user . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0  AND
                  p.`cancelled` = 0
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
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  p.created, p.expected_date, p.rating, p.project_status_id,p.contracted, p.completed
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
                  p.`rating` = \'' . $rating . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0  AND
                  p.`cancelled` = 0
                GROUP BY p.project_id
                ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
    }

    /**
     * Get Un Rated projects
     *
     * @param $args
     *
     * @return mixed
     */
    private function getCustomerData( $rating, $user = '' )
    {
        if ( !empty($user) )
        {
            $sql
                = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` = \'' . $rating . '\' AND
                  c.client_id = \'' . $user . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
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
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id,p.contracted, p.completed
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
                  p.`rating` = \'' . $rating . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
                GROUP BY p.project_id
                ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
    }

    /**
     * Get All ratings data for user and all
     *
     * @param $user
     */
    private function getAllRatingsData( $user )
    {
        if ( !empty($user) )
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
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` IN (0,1,2,3) AND
                  c.user_id = \'' . $user . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0  AND
                        p.`cancelled` = 0
                Group by p.project_id
                ORDER BY p.rating DESC, p.expected_date, p.created';
        }
        else
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
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0  AND
                  p.`cancelled` = 0
              GROUP By p.project_id
                ORDER BY p.rating DESC,p.expected_date, p.created';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        $data = $stmt->fetchAll( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );

        $subtotal = Array();

        if ( !empty($data) )
        {
            foreach ( $data as $key => $value )
            {
                $subtotal[$value['rating']][] = $value;
            }
        }

        $data['subtotal'] = $subtotal;

        return $data;
    }

    /**
     * Get All ratings data for user and all based on customer
     *
     * @param $user
     */
    private function getAllCustomerSalesData( $user )
    {
        if ( !empty($user) )
        {
            $sql
                = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed, c.client_id
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
                  p.`rating` IN (0,1,2,3) AND
                  c.client_id = \'' . $user . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
                GROUP BY p.project_id
                ORDER BY p.`rating` desc, p.expected_date, p.created';
        }
        else
        {
            $sql
                = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed, c.client_id
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
                  p.`rating` IN (0,1,2,3) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
                GROUP BY p.project_id
                ORDER BY p.rating DESC, p.expected_date, p.created';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
    }

    /**
     * Get All ratings data for user and all
     *
     * @param $user
     */
    private function getAllJobsData( $user )
    {
        if ( !empty($user) )
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
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` IN (7,8,9) AND
                  c.user_id = \'' . $user . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
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
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0
                GROUP BY p.project_id
                ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
    }

    /**
     * Get All ratings data for user and all
     *
     * @param $user
     */
    private function getAllJobsCustomerData( $user )
    {
        if ( !empty($user) )
        {
            $sql
                = 'SELECT
                  p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` IN (7,8,9) AND
                  c.client_id = \'' . $user . '\' AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
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
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(Sy.ppu * Sy.quantity * Sp.quantity) price,
                  SUM(Sy.cpu * Sy.quantity * Sp.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed
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
                  p.`rating` IN (7,8,9) AND
                  p.`test` = 0 AND
                  p.`exclude_from_reporting` = 0 AND
                  p.`cancelled` = 0
                GROUP BY p.project_id
                ORDER BY p.created DESC';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        return $stmt->fetchAll( \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY );
    }


    /**
     * List of monthly report
     */
    public function monthlyAction()
    {

        $rating = $this->params()->fromRoute( 'rating', '' );
        $user   = $this->params()->fromRoute( 'user' );
        $month  = $this->params()->fromRoute( 'month' );

        $current_year = date( 'Y' );

        if ( $month < date( 'm' ) )
        {
            $current_year++;
        }

        $this->setCaption( 'Monthly Report for ' . $month . "-" . $current_year );

        if ( $user )
        {
            $sql
                = 'SELECT
                p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(sy.ppu * sy.quantity * s.quantity) price,
                  SUM(sy.cpu * sy.quantity * s.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed

                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    INNER JOIN `User` u ON u.`user_id` = c.`user_id`
                    WHERE
                        p.`rating`  IN (0,1,2,3) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0 AND
                        MONTH(p.expected_date) = \'' . $month . '\' AND
                        YEAR(p.expected_date) = \'' . $current_year . '\' AND
                        u.`user_id` =  \'' . $user . '\'
                        Group by p.rating, p.project_id
                        order by p.rating desc, p.expected_date asc';
        }
        else
        {
            $sql
                = 'SELECT
                p.project_id,
                  p.name          project_name,
                  c.name          client_name,
                  CONCAT(u.forename, " ", u.surname) user_name,
                  c.client_id,
                  SUM(sy.ppu * sy.quantity * s.quantity) price,
                  SUM(sy.cpu * sy.quantity * s.quantity) cost,
                  p.created, p.expected_date, p.rating, p.project_status_id, p.contracted, p.completed

                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    INNER JOIN `User` u ON u.`user_id` = c.`user_id`
                    WHERE
                        p.`rating`  IN (0,1,2,3) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0 AND
                        MONTH(p.expected_date) = \'' . $month . '\' AND
                        YEAR(p.expected_date) = \'' . $current_year . '\'
                        Group by p.rating, p.project_id
                        order by p.rating desc, p.expected_date asc';
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
        $stmt->execute();

        $data = $stmt->fetchAll();

        $sub_total = Array();

        foreach ( $data as $key => $value )
        {
            $sub_total[$value['rating']][] = $value;
        }

        $this->getView()
            ->setVariable( 'data', $data )
            ->setVariable( 'sub_total', $sub_total )
            ->setVariable( 'title', 'Monthly ' );

        return $this->getView();

    }

    /**
     * Detail Action lists
     *
     * @throws \Exception
     */
    public function detailAction()
    {
        $user = $this->params()->fromRoute( 'user', 0 );
        if ( !is_numeric( $user ) )
        {
            throw new \Exception( 'Illegal user route' );
        }

        $type    = $this->params()->fromRoute( 'type', '' );
        $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' );

        if ( strtolower( trim( $type ) ) == 'customer' )
        {
            $data['chartData'] = $this->chartDataAction( $user, 'customer' );
            $data['data'] = $project->findYearlySalesOutlook( $user );
        }
        else
        {
            $data['chartData'] = $this->chartDataAction( $user );
            $data['data'] = $project->findYearlyOutlookCustomerWise( $user );
        }



        //echo '<pre>'; print_r($data);exit;
        $this->setCaption( '12 Months Outlook Report' );
        $this->getView()
            ->setVariable( 'data', $data )
            ->setVariable( 'user', $user );

        return $this->getView();
    }

    /**
     * Get chart data through ajax call
     */
    private function chartDataAction( $user, $type = '' )
    {

        $category         = array();
        $category['name'] = 'Month';

        $series1         = array();
        $series1['name'] = 'Un-Rated';
        $series1['data'] = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

        $series2         = array();
        $series2['name'] = 'Red Rated';
        $series2['data'] = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

        $series3         = array();
        $series3['name'] = 'Amber Rated';
        $series3['data'] = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

        $series4         = array();
        $series4['name'] = 'Green Rated';
        $series4['data'] = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

        $total = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );

        for ( $i = 0; $i <= 11; $i++ )
        {
            $timestamp          = mktime( 0, 0, 0, date( 'm' ) + $i, 1, date( 'Y' ) );
            $category['data'][] = '"' . date( 'M', $timestamp ) . '"';
            if ( $user )
            {
                if ( $type == 'customer' )
                {
                    $sql
                        = 'SELECT SUM(sy.`ppu` * sy.`quantity` * s.quantity) `value`, p.rating
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                        INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                        INNER JOIN `User` u ON u.user_id = c.user_id
                        WHERE
                            p.`rating`  IN (0,1,2,3) AND
                            p.`test` = 0 AND
                            p.`exclude_from_reporting` = 0 AND
                            c.`client_id` = \'' . $user . '\' AND
                            MONTH(p.expected_date) = \'' . date( 'm', $timestamp ) . '\' AND
                            YEAR(p.expected_date) = \'' . date( 'Y', $timestamp ) . '\'
                            Group by p.rating';
                }
                else
                {
                    $sql
                        = 'SELECT SUM(sy.`ppu` * sy.`quantity` * s.quantity) `value`, p.rating
                        FROM System sy
                        INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                        INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                        INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                        INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                        INNER JOIN `User` u ON u.user_id = c.user_id
                        WHERE
                            p.`rating`  IN (0,1,2,3) AND
                            p.`test` = 0 AND
                            p.`exclude_from_reporting` = 0 AND
                            u.`user_id` = \'' . $user . '\' AND
                            MONTH(p.expected_date) = \'' . date( 'm', $timestamp ) . '\' AND
                            YEAR(p.expected_date) = \'' . date( 'Y', $timestamp ) . '\'
                            Group by p.rating';
                }
            }
            else
            {

                $sql
                    = 'SELECT SUM(sy.`ppu` * sy.`quantity` * s.quantity) `value`, p.rating
                    FROM System sy
                    INNER JOIN `Space` s ON s.`space_id` = sy.`space_id`
                    INNER JOIN `Project` p ON p.`project_id` = s.`project_id`
                    INNER JOIN `Project_Status` ps ON ps.`project_status_id` = p.`project_status_id`
                    INNER JOIN `Client` c ON c.`client_id` = p.`client_id`
                    WHERE
                        p.`rating`  IN (0,1,2,3) AND
                        p.`test` = 0 AND
                        p.`exclude_from_reporting` = 0 AND
                        MONTH(p.expected_date) = \'' . date( 'm', $timestamp ) . '\' AND
                        YEAR(p.expected_date) = \'' . date( 'Y', $timestamp ) . '\'
                        Group by p.rating';

            }

            $stmt = $this->getEntityManager()->getConnection()->prepare( $sql );
            $stmt->execute();

            $data = $stmt->fetchAll();
            if ( !empty($data) )
            {
                foreach ( $data as $arr )
                {
                    if ( $arr['rating'] == 0 )
                    {
                        $series1['data'][$i] = empty($arr['value']) ? 0 : $arr['value'];
                    }

                    if ( $arr['rating'] == 1 )
                    {
                        $series2['data'][$i] = empty($arr['value']) ? 0 : $arr['value'];
                    }

                    if ( $arr['rating'] == 2 )
                    {
                        $series3['data'][$i] = empty($arr['value']) ? 0 : $arr['value'];
                    }

                    if ( $arr['rating'] == 3 )
                    {
                        $series4['data'][$i] = empty($arr['value']) ? 0 : $arr['value'];
                    }

                    $total[$i] += empty($arr['value']) ? 0 : $arr['value'];
                }
            }
            else
            {
                $series1['data'][$i] = 0;
                $series2['data'][$i] = 0;
                $series3['data'][$i] = 0;
                $series4['data'][$i] = 0;
            }
        }


        $result = array();
        array_push( $result, $category );
        array_push( $result, $series1 );
        array_push( $result, $series2 );
        array_push( $result, $series3 );
        array_push( $result, $series4 );

        foreach ( $result as $key => $value )
        {
            $result[$key]['data'] = implode( ',', $value['data'] );
        }

        array_push( $result, $total );

        return $result;
    }
}
