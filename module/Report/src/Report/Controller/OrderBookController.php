<?php
namespace Report\Controller;

use Application\Controller\AuthController;
use Contact\Entity\Mode;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;


class OrderBookController extends AuthController
{
    /**
     * @var year
     */
    private $year;

    /**
     * @var month
     */
    private $month; // same will be treated as quarter in quarterly report

    public function indexAction()
    {
        exit('Here');
    }

    /**
     * Monthly List of of Order Book
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function listAction()
    {
        $this->setCaption( 'Order Book' );
        $this->getUriData();
        $em = $this->getEntityManager();
        $re = $em->getRepository( '\Report\Entity\Report' );

        $rows = $re->getOrderBookListing( $this->year, $this->month );
        $targets = $em->getRepository('Sales\Entity\Target')->findTarget();
        $users = [];
        $data = array();

        if ( !empty($rows) )
        {
            foreach ( $rows as $row )
            {
                $data[$row['department_name']][$row['user_id']]['data'][] = $row;
                $target = $em->getRepository( '\Sales\Entity\Target' )->findTarget( $row['user_id'] );
                if ( !empty($target) )
                {
                    $data[$row['department_name']][$row['user_id']]['target'] = $target[$row['user_id']];
                    $data[$row['department_name']][$row['user_id']]['sales']  = array_sum( $target[$row['user_id']]['sales'] );
                    $data[$row['department_name']][$row['user_id']]['margin'] = array_sum( $target[$row['user_id']]['margin'] );
                    unset($targets[$row['user_id']]);
                }
                else
                {
                    $data[$row['department_name']][$row['user_id']]['target'] = [];
                    $data[$row['department_name']][$row['user_id']]['sales']  = 0;
                    $data[$row['department_name']][$row['user_id']]['margin'] = 0;
                }
            }
        }

        if ( !empty($targets) )
        {
            foreach($targets as $t)
            {

                $data[$t['department_name']][$t['user']]['data'] = Array();
                $data[$t['department_name']][$t['user']]['target'] = $t;
                $data[$t['department_name']][$t['user']]['sales'] = array_sum($t['sales']);
                $data[$t['department_name']][$t['user']]['margin'] = array_sum($t['margin']);

            }
        }

        //echo '<pre>';        print_r($data);        exit;

        $this->getView()
            ->setVariable( 'data', $data )
            ->setVariable( 'year', $this->year )
            ->setVariable( 'month', $this->month );

        return $this->getView();
    }

    /**
     * Export Monthly Order Book
     *
     * @return \Zend\Mvc\Controller\AbstractController
     * @throws \Exception
     */
    public function exportOrderBookAction()
    {
        $this->getUriData();

        $em   = $this->getEntityManager();
        $re   = $em->getRepository( '\Report\Entity\Report' );
        $rows = $re->getOrderBookListing( $this->year, $this->month );

        $data = array();
        if ( !empty($rows) )
        {
            foreach ( $rows as $row )
            {
                $target = $em->getRepository( '\Sales\Entity\Target' )->findTarget( $row['user_id'] );
                $data[$row['department_name']][$row['user_id']]['data'][] = $row;
                $data[$row['department_name']][$row['user_id']]['target'] = $target[$row['user_id']];
                $data[$row['department_name']][$row['user_id']]['sales']  = array_sum( $target[$row['user_id']]['sales'] );
                $data[$row['department_name']][$row['user_id']]['margin'] = array_sum( $target[$row['user_id']]['margin'] );
            }
        }

        $filename = 'Order-Book-Monthly_' . $this->year . '_' . $this->month . '.csv';

        return $this->downloadOrderBookCSV( $data, $filename );
    }

    /**
     * Quarterly list or order book
     *
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function quarterlyListAction()
    {
        $this->setCaption( 'Order Book By Quarter' );
        $this->getUriData();
        $quarter = $this->month; // month is capturing quarter in constructor function

        if ( $quarter > 4 )
        {
            throw new \Exception( 'Quarter must be between 1 to 4' );
        }

        $em = $this->getEntityManager();
        $re = $em->getRepository( '\Report\Entity\Report' );
        $targets = $em->getRepository('Sales\Entity\Target')->findQuarterlyTarget();
        $rows = $re->getOrderBookQuarterListing( $this->year, $quarter );
        //echo '<pre>';        print_r($rows);        exit;

        $data = array();
        if ( !empty($rows) )
        {
            foreach ( $rows as $row )
            {
                $target = $em->getRepository( '\Sales\Entity\Target' )->findQuarterlyTarget( $row['user_id'] );
                if ( !empty($target) )
                {
                    $data[$row['department_name']][$row['user_id']]['data'][] = $row;
                    $data[$row['department_name']][$row['user_id']]['target'] = $target[$row['user_id']];
                    $data[$row['department_name']][$row['user_id']]['sales']  = array_sum( $target[$row['user_id']]['sales'] );
                    $data[$row['department_name']][$row['user_id']]['margin'] = array_sum( $target[$row['user_id']]['margin'] );
                    unset($targets[$row['user_id']]);
                }
                else
                {
                    $data[$row['department_name']][$row['user_id']]['data'][] = $row;
                    $data[$row['department_name']][$row['user_id']]['target'] = [];
                    $data[$row['department_name']][$row['user_id']]['sales']  = 0;
                    $data[$row['department_name']][$row['user_id']]['margin'] = 0;
                }
            }
        }

        //echo '<pre>';        print_r($targets);        exit;
        if ( !empty($targets) )
        {
            foreach($targets as $t)
            {

                $data[$t['department_name']][$t['user']]['data']   = Array();
                $data[$t['department_name']][$t['user']]['target'] = $t;
                $data[$t['department_name']][$t['user']]['sales']  = array_sum( $t['sales'] );
                $data[$t['department_name']][$t['user']]['margin'] = array_sum( $t['margin'] );

            }
        }

        $this->getView()
            ->setVariable( 'data', $data )
            ->setVariable( 'year', $this->year )
            ->setVariable( 'month', $quarter );

        return $this->getView();
    }

    /**
     * Export Quarterly Order Book
     *
     * @return \Zend\Mvc\Controller\AbstractController
     * @throws \Exception
     */
    public function exportQuarterlyOrderBookAction()
    {
        $this->getUriData();

        // getting report entity
        $re      = $this->getEntityManager()->getRepository( '\Report\Entity\Report' );
        $quarter = $this->month;

        $data = $re->getOrderBookQuarterListing( $this->year, $quarter );

        $filename = 'Quarterly-Order-Book-Monthly_' . $this->year . '_' . $this->month . '.csv';

        return $this->downloadCSV( $data, $filename );
    }

    private function downloadCSV( $data, $filename )
    {
        $export   = Array();
        $export[] = array(
            '"Reference"',
            '"Project"',
            '"Client"',
            '"Project Value"',
            '"Owner"',
            '"Started"',
            '"Completed"'
        );
        $total    = 0;
        foreach ( $data as $item )
        {
            $export[] = array(
                str_pad( $item['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $item['project_id'], 5, "0", STR_PAD_LEFT ),
                '"' . $item['project_name'] . '"',
                '"' . $item['client_name'] . '"',
                '"' . number_format( $item['price'], 2 ) . '"',
                '"' . $item['user_name'] . '"',
                '"' . date( 'd M, Y', strtotime( $item['contracted'] ) ) . '"',
                '"' . (!empty($item['completed']) ? date( 'd M, Y', strtotime( $item['completed'] ) ) : "") . '"'
            );

            $total += $item['price'];
        }

        $export[] = Array(
            '',
            '',
            'Total',
            '"' . number_format( $total, 2 ) . '"'
        );

        return $this->prepareCSVResponse( $export, $filename );
    }

    private function downloadOrderBookCSV( $data, $filename )
    {
        //echo '<pre>';        print_r($data);        exit;

        $export   = Array();
        $export[] = array(
            '"Reference"',
            '"Project"',
            '"Client"',
            '"Project Value"',
            '"Margin Value"',
            '"Margin %"',
            '"Owner"',
            '"Department"',
            '"Started"',
            '"Completed"'
        );
        $total    = 0;


        if ( !empty($data) )
        {
            $total             = 0; // Complete total
            $margin            = 0; // Complete margin
            $totalSalesTarget  = 0; // Grand Total of sales target
            $totalMarginTarget = 0; // Grand Total of margin target
            foreach ( $data as $departmentKey => $departmentArray )
            {
                $departmentTargetValue  = 0; // Department Target Value
                $departmentTargetMargin = 0; // Department Target Margin
                $departmentTotal        = 0; // Deparment Total
                $departmentMargin       = 0; // Department Margin

                foreach ( $departmentArray as $ownerKey => $ownerArray )
                {
                    $ownerTotal  = 0;
                    $ownerMargin = 0;

                    foreach ( $ownerArray['data'] as $item )
                    {
                        $marginItem = 0;
                        if ( $item['priceMCD'] > 0 )
                        {
                            $marginItem = number_format( round( (($item['priceMCD'] - $item['cost']) / $item['priceMCD']) * 100, 2 ), 2, '.', '' );
                        }

                        $export[] = array(
                            str_pad( $item['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $item['project_id'], 5, "0", STR_PAD_LEFT ),
                            '"' . $item['project_name'] . '"',
                            '"' . $item['client_name'] . '"',
                            '"' . number_format( $item['priceMCD'], 2 ) . '"',
                            '"' . number_format( $item['priceMCD'] - $item['cost'], 2 ) . '"',
                            '"' . $marginItem . ' %"',
                            '"' . $item['user_name'] . '"',
                            '"' . $item['department_name'] . '"',
                            '"' . date( 'd M, Y', strtotime( $item['contracted'] ) ) . '"',
                            '"' . (!empty($item['completed']) ? date( 'd M, Y', strtotime( $item['completed'] ) ) : "") . '"'
                        );

                        $ownerTotal += $item['priceMCD'];
                        $ownerMargin += $item['priceMCD'] - $item['cost'];
                    }

                    $export[] = array(
                        '',
                        '',
                        '"Sub Total Owner"',
                        '"' . number_format( $ownerTotal, 2 ) . '"',
                        '"' . number_format( $ownerMargin, 2 ) . '"',
                        '"' . number_format( ($ownerMargin / $ownerTotal) * 100, 2 ) . ' %"',
                    );

                    $ownerSalesValue  = 0;
                    $ownerMarginValue = 0;
                    if ( $this->month > 0 )
                    {
                        $ownerSalesValue  = $ownerArray['target']['sales'][$this->month];
                        $ownerMarginValue = $ownerArray['target']['margin'][$this->month];
                    }
                    else
                    {
                        $ownerSalesValue  = $ownerArray['sales'];
                        $ownerMarginValue = $ownerArray['margin'];
                    }


                    $export[] = array(
                        '',
                        '',
                        '"Target"',
                        '"' . number_format( $ownerSalesValue, 2 ) . '"',
                        '"' . number_format( $ownerMarginValue, 2 ) . '"',
                        '"' . number_format( ($ownerMarginValue / $ownerSalesValue) * 100, 2 ) . ' %"',
                    );

                    $export[] = array(
                        '',
                        '',
                        '"Variance"',
                        '"' . number_format( ($ownerTotal / $ownerSalesValue) * 100, 2 ) . ' %"',
                        '"' . number_format( ($ownerMargin / $ownerMarginValue) * 100, 2 ) . '%"',
                    );

                    $departmentTotal += $ownerTotal;
                    $departmentMargin += $ownerMargin;

                    if ( $this->month > 0 )
                    {
                        $departmentTargetValue += $ownerArray['target']['sales'][$this->month];
                        $departmentTargetMargin += $ownerArray['target']['margin'][$this->month];
                    }
                    else
                    {
                        $departmentTargetValue += $ownerArray['sales'];
                        $departmentTargetMargin += $ownerArray['margin'];

                    }
                } // End of owner loop

                $export[] = array(
                    '',
                    '',
                    '"Sub Total Department"',
                    '"' . number_format( $departmentTotal, 2 ) . '"',
                    '"' . number_format( $departmentMargin, 2 ) . '"',
                    '"' . number_format( ($departmentMargin / $departmentTotal) * 100, 2 ) . ' %"',
                );


                $export[] = array(
                    '',
                    '',
                    '"Target Department"',
                    '"' . number_format( $departmentTargetValue, 2 ) . '"',
                    '"' . number_format( $departmentTargetMargin, 2 ) . '"',
                    '"' . number_format( ($departmentTargetMargin / $departmentTargetValue) * 100, 2 ) . ' %"',
                );

                $export[] = array(
                    '',
                    '',
                    '"Variance"',
                    '"' . number_format( ($departmentTotal / $departmentTargetValue) * 100, 2 ) . ' %"',
                    '"' . number_format( ($departmentMargin / $departmentTargetMargin) * 100, 2 ) . ' %"',
                );

                $total += $departmentTotal;
                $margin += $departmentMargin;
                $totalSalesTarget += $departmentTargetValue;
                $totalMarginTarget += $departmentTargetMargin;
            } // End of Department Loop

            $export[] = array(
                '',
                '',
                '"Grand Total"',
                '"' . number_format( $total, 2 ) . '"',
                '"' . number_format( $margin, 2 ) . '"',
                '"' . number_format( ($margin / $total) * 100, 2 ) . ' %"',
            );


            $export[] = array(
                '',
                '',
                '"Grand Total Target"',
                '"' . number_format( $totalSalesTarget, 2 ) . '"',
                '"' . number_format( $totalMarginTarget, 2 ) . '"',
                '"' . number_format( ($totalMarginTarget / $totalSalesTarget) * 100, 2 ) . ' %"',
            );

            $export[] = array(
                '',
                '',
                '"Variance"',
                '"' . number_format( ($total / $totalSalesTarget) * 100, 2 ) . ' %"',
                '"' . number_format( ($margin / $totalMarginTarget) * 100, 2 ) . '%"',
            );


        } //End of main loop
        return $this->prepareCSVResponse( $export, $filename );

        /*




        foreach ( $data as $item )
        {
            $margin = 0;
            if ( $item['priceMCD'] > 0 )
            {
                $margin = number_format( round( (($item['priceMCD'] - $item['cost']) / $item['priceMCD']) * 100, 2 ), 2, '.', '' );
            }

            $export[] = array(
                str_pad( $item['client_id'], 5, "0", STR_PAD_LEFT ) . '-' . str_pad( $item['project_id'], 5, "0", STR_PAD_LEFT ),
                '"' . $item['project_name'] . '"',
                '"' . $item['client_name'] . '"',
                '"' . number_format( $item['priceMCD'], 2 ) . '"',
                '"' . number_format( $item['priceMCD'] - $item['cost'], 2 ) . '"',
                '"' . $margin . '%"',
                '"' . $item['user_name'] . '"',
                '"' . $item['department_name'] . '"',
                '"' . date( 'd M, Y', strtotime( $item['contracted'] ) ) . '"',
                '"' . (!empty($item['completed']) ? date( 'd M, Y', strtotime( $item['completed'] ) ) : "") . '"'
            );

            $total += $item['price'];
        }

        $export[] = Array(
            '',
            '',
            'Total',
            '"' . number_format( $total, 2 ) . '"'
        );

        return $this->prepareCSVResponse( $export, $filename );
        */
    }

    private function getUriData()
    {
        $this->year = $this->params()->fromRoute( 'year', '' );

        if ( empty($this->year) )
        {
            throw new \Exception( 'Invalid year route' );
        }

        $this->month = $this->params()->fromRoute( 'month', 0 );

        if ( !is_numeric( $this->month ) )
        {
            throw new \Exception( 'Month must be a number' );
        }
    }
}
