<?php
namespace Report\Controller;

use Application\Controller\AuthController;
use Contact\Entity\Mode;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZendGData\App\Exception;


class ReportQuarterlyController extends AuthController
{
    private $quarter;
    private $year;
    private $export;

    public function indexAction()
    {
        exit('Here');
    }


    /**
     * Get list of clients based on quarters and years
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function clientsAction()
    {
        $this->getUriData();
        $this->setCaption( 'Client Quarterly Report' );
        $client = $this->getEntityManager()->getRepository( '\Client\Entity\Client' );

        $clients = $client->findByQuarter( $this->quarter, $this->year );

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Client Name"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($clients) )
            {
                foreach ( $clients as $client )
                {
                    $data[] = Array(
                        '"' . $client['name'] . '"',
                        '"' . $client['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $client['created'] ) ) . '"'
                    );
                }
            }

            $filename = 'clients_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'clients', $clients )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();

    }

    /**
     * Print list of contacts based on quarter and year
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function contactsAction()
    {
        $this->setCaption( 'Contact Quarterly Report' );
        $this->getUriData();
        $contact  = $this->getEntityManager()->getRepository( 'Contact\Entity\Contact' );
        $contacts = $contact->findByQuarter( $this->quarter, $this->year );

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Contact Name"',
                '"Client Name"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($contacts) )
            {
                foreach ( $contacts as $contact )
                {
                    $data[] = Array(
                        '"' . $contact['forename'] . ' ' . $contact['surname'] . '"',
                        '"' . $contact['client_name'] . '"',
                        '"' . $contact['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $contact['created'] ) ) . '"'
                    );
                }
            }

            $filename = 'contacts_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'contacts', $contacts )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();
    }

    /**
     * Print list of quotations based on quarter and year
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function quotationsAction()
    {
        $this->setCaption( 'Quotations Quarterly Report' );
        $this->getUriData();
        $project    = $this->getEntityManager()->getRepository( 'Project\Entity\Project' );
        $quotations = $project->findQuotationsByQuarter( $this->quarter, $this->year );

        foreach ( $quotations as $key => $val )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $val['project_id'] );

            $ms      = $this->getServiceLocator()->get( 'Model' );
            $payback = $ms->payback( $project );

            $quotations[$key]['figures'] = $payback['figures'];

        }

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Document ID"',
                '"Project Name"',
                '"Client"',
                '"Value"',
                '"Margin"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($quotations) )
            {
                $total = 0;
                foreach ( $quotations as $quotation )
                {
                    $margin      = 0;
                    $tmpPpuTotal = $quotation['figures']['cost'];
                    $tmpCpuTotal = $quotation['cost'];
                    if ( $tmpPpuTotal )
                    {
                        $prjMargin = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                        $margin    = number_format( $prjMargin, 2 ) . " %";
                    }
                    else
                    {
                        $margin = '0 %';
                    }

                    $data[] = Array(
                        '"' . $quotation['document_list_id'] . '"',
                        '"' . $quotation['project_name'] . '"',
                        '"' . $quotation['client_name'] . '"',
                        '"' . number_format( $quotation['figures']['cost'], 2 ) . '"',
                        '"' . $margin . '"',
                        '"' . $quotation['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $quotation['created'] ) ) . '"'
                    );

                    $total += $quotation['figures']['cost'];
                }

                $data[] = Array(
                    '',
                    '',
                    'Total',
                    '"' . number_format( $total, 2 ) . '"'
                );
            }

            $filename = 'quotations_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'quotations', $quotations )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();
    }


    /**
     * Print list of prosposals based on quarter and year
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function proposalsAction()
    {
        $this->setCaption( 'Proposals Quarterly Report' );
        $this->getUriData();
        $project   = $this->getEntityManager()->getRepository( 'Project\Entity\Project' );
        $proposals = $project->findProposalsByQuarter( $this->quarter, $this->year );

        foreach ( $proposals as $key => $val )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $val['project_id'] );

            $ms      = $this->getServiceLocator()->get( 'Model' );
            $payback = $ms->payback( $project );

            $proposals[$key]['figures'] = $payback['figures'];

        }

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Document ID"',
                '"Project Name"',
                '"Client"',
                '"Value"',
                '"Margin"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($proposals) )
            {
                $total = 0;
                foreach ( $proposals as $proposal )
                {
                    $margin      = 0;
                    $tmpPpuTotal = $proposal['figures']['cost'];
                    $tmpCpuTotal = $proposal['cost'];
                    if ( $tmpPpuTotal )
                    {
                        $prjMargin = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                        $margin    = number_format( $prjMargin, 2 ) . " %";
                    }
                    else
                    {
                        $margin = '0 %';
                    }

                    $data[] = Array(
                        '"' . $proposal['document_list_id'] . '"',
                        '"' . $proposal['project_name'] . '"',
                        '"' . $proposal['client_name'] . '"',
                        '"' . number_format( $proposal['figures']['cost'], 2 ) . '"',
                        '"' . $margin . '"',
                        '"' . $proposal['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $proposal['created'] ) ) . '"'
                    );

                    $total += $proposal['figures']['cost'];
                }

                $data[] = Array(
                    '',
                    '',
                    'Total',
                    '"' . number_format( $total, 2 ) . '"'
                );
            }

            $filename = 'proposals_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'proposals', $proposals )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();
    }

    /**
     * Print list of all projects based on quarter and year
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function allprojectsAction()
    {
        $this->setCaption( 'All Projects Quarterly Report' );
        $this->getUriData();
        $project  = $this->getEntityManager()->getRepository( 'Project\Entity\Project' );
        $projects = $project->findAllByQuarter( $this->quarter, $this->year );

        foreach ( $projects as $key => $val )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $val['project_id'] );

            $ms      = $this->getServiceLocator()->get( 'Model' );
            $payback = $ms->payback( $project );

            $projects[$key]['figures'] = $payback['figures'];

        }

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Reference"',
                '"Project"',
                '"Client"',
                '"Value"',
                '"Margin"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($projects) )
            {
                $total = 0;
                foreach ( $projects as $project )
                {
                    $margin      = 0;
                    $tmpPpuTotal = $project['figures']['cost'];
                    $tmpCpuTotal = $project['cost'];
                    if ( $tmpPpuTotal )
                    {
                        $prjMargin = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                        $margin    = number_format( $prjMargin, 2 ) . " %";
                    }
                    else
                    {
                        $margin = '0 %';
                    }

                    $data[] = Array(
                        '"' . str_pad( $project['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $project['project_id'], 5, "0", STR_PAD_LEFT ) . '"',
                        '"' . $project['project_name'] . '"',
                        '"' . $project['client_name'] . '"',
                        '"' . number_format( $project['figures']['cost'], 2 ) . '"',
                        '"' . $margin . '"',
                        '"' . $project['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $project['created'] ) ) . '"'
                    );

                    $total += $project['figures']['cost'];
                }

                $data[] = Array(
                    '',
                    '',
                    'Total',
                    '"' . number_format( $total, 2 ) . '"'
                );
            }

            $filename = 'All Projects_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'projects', $projects )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();
    }

    /**
     * Print list of won projects based on quarter and year
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function wonprojectsAction()
    {
        $this->setCaption( 'Won Projects Quarterly Report' );
        $this->getUriData();
        $project  = $this->getEntityManager()->getRepository( 'Project\Entity\Project' );
        $projects = $project->findWonByQuarter( $this->quarter, $this->year );

        foreach ( $projects as $key => $val )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $val['project_id'] );

            $ms      = $this->getServiceLocator()->get( 'Model' );
            $payback = $ms->payback( $project );

            $projects[$key]['figures'] = $payback['figures'];
        }

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Reference"',
                '"Project"',
                '"Client"',
                '"Value"',
                '"Margin"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($projects) )
            {
                $total = 0;
                foreach ( $projects as $project )
                {
                    $margin      = 0;
                    $tmpPpuTotal = $project['figures']['cost'];
                    $tmpCpuTotal = $project['cost'];
                    if ( $tmpPpuTotal )
                    {
                        $prjMargin = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                        $margin    = number_format( $prjMargin, 2 ) . " %";
                    }
                    else
                    {
                        $margin = '0 %';
                    }

                    $data[] = Array(
                        '"' . str_pad( $project['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $project['project_id'], 5, "0", STR_PAD_LEFT ) . '"',
                        '"' . $project['project_name'] . '"',
                        '"' . $project['client_name'] . '"',
                        '"' . number_format( $project['figures']['cost'], 2 ) . '"',
                        '"' . $margin . '"',
                        '"' . $project['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $project['created'] ) ) . '"'
                    );

                    $total += $project['figures']['cost'];
                }

                $data[] = Array(
                    '',
                    '',
                    'Total',
                    '"' . number_format( $total, 2 ) . '"'
                );
            }

            $filename = 'Won Projects_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'projects', $projects )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();
    }


    /**
     * Print list of lost projects based on quarter and year
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function lostprojectsAction()
    {
        $this->setCaption( 'Lost Projects Quarterly Report' );
        $this->getUriData();
        $project  = $this->getEntityManager()->getRepository( 'Project\Entity\Project' );
        $projects = $project->findLostByQuarter( $this->quarter, $this->year );

        foreach ( $projects as $key => $val )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $val['project_id'] );

            $ms      = $this->getServiceLocator()->get( 'Model' );
            $payback = $ms->payback( $project );

            $projects[$key]['figures'] = $payback['figures'];

        }

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Reference"',
                '"Project"',
                '"Client"',
                '"Value"',
                '"Margin"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($projects) )
            {
                $total = 0;
                foreach ( $projects as $project )
                {
                    $margin      = 0;
                    $tmpPpuTotal = $project['figures']['cost'];
                    $tmpCpuTotal = $project['cost'];
                    if ( $tmpPpuTotal )
                    {
                        $prjMargin = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                        $margin    = number_format( $prjMargin, 2 ) . " %";
                    }
                    else
                    {
                        $margin = '0 %';
                    }

                    $data[] = Array(
                        '"' . str_pad( $project['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $project['project_id'], 5, "0", STR_PAD_LEFT ) . '"',
                        '"' . $project['project_name'] . '"',
                        '"' . $project['client_name'] . '"',
                        '"' . number_format( $project['figures']['cost'], 2 ) . '"',
                        '"' . $margin . '"',
                        '"' . $project['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $project['created'] ) ) . '"'
                    );

                    $total += $project['figures']['cost'];
                }

                $data[] = Array(
                    '',
                    '',
                    'Total',
                    '"' . number_format( $total, 2 ) . '"'
                );
            }

            $filename = 'Lost Projects_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'projects', $projects )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();
    }

    /**
     * Print list of open projects based on quarter and year
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function openprojectsAction()
    {
        $this->setCaption( 'Open Projects Quarterly Report' );
        $this->getUriData();
        $project  = $this->getEntityManager()->getRepository( 'Project\Entity\Project' );
        $projects = $project->findOpenByQuarter( $this->quarter, $this->year );

        foreach ( $projects as $key => $val )
        {
            $project = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findByProjectId( $val['project_id'] );

            $ms      = $this->getServiceLocator()->get( 'Model' );
            $payback = $ms->payback( $project );

            $projects[$key]['figures'] = $payback['figures'];

        }

        if ( strtolower( $this->export ) == 'csv' )
        {
            $data[] = array(
                '"Reference"',
                '"Project"',
                '"Client"',
                '"Value"',
                '"Margin"',
                '"Owner"',
                '"Date"'
            );

            if ( !empty($projects) )
            {
                $total = 0;
                foreach ( $projects as $project )
                {
                    $margin      = 0;
                    $tmpPpuTotal = $project['figures']['cost'];
                    $tmpCpuTotal = $project['cost'];
                    if ( $tmpPpuTotal )
                    {
                        $prjMargin = (($tmpPpuTotal - $tmpCpuTotal) / $tmpPpuTotal) * 100;
                        $margin    = number_format( $prjMargin, 2 ) . " %";
                    }
                    else
                    {
                        $margin = '0 %';
                    }

                    $data[] = Array(
                        '"' . str_pad( $project['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $project['project_id'], 5, "0", STR_PAD_LEFT ) . '"',
                        '"' . $project['project_name'] . '"',
                        '"' . $project['client_name'] . '"',
                        '"' . number_format( $project['figures']['cost'], 2 ) . '"',
                        '"' . $margin . '"',
                        '"' . $project['user_name'] . '"',
                        '"' . date( 'd M, Y', strtotime( $project['created'] ) ) . '"'
                    );

                    $total += $project['figures']['cost'];
                }

                $data[] = Array(
                    '',
                    '',
                    'Total',
                    '"' . number_format( $total, 2 ) . '"'
                );
            }

            $filename = 'Open Projects_' . $this->quarter . '_' . $this->year . '.csv';

            $response = $this->prepareCSVResponse( $data, $filename );

            return $response;
        }

        $this->getView()
            ->setVariable( 'projects', $projects )
            ->setVariable( 'quarter', $this->quarter )
            ->setVariable( 'year', $this->year );

        return $this->getView();
    }


    /**
     * get uri data and set class variable values
     *
     * @throws \Exception
     */
    private function getUriData()
    {
        $this->quarter = $this->params()->fromRoute( 'quarter' );
        $this->year    = $this->params()->fromRoute( 'year' );
        $this->export  = $this->params()->fromRoute( 'export', '' );
        if ( empty($this->quarter) || empty($this->year) )
        {
            throw new \Exception( 'Illegal route parameters' );
        }
    }
}
