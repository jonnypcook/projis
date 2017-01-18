<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Tools\Controller;

use Application\Entity\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Client;

class EmergencyController extends AbstractActionController
{
    static $NEWLINE = "\r\n";
    static $URIS = array(
        'projects' => 'http://portal.liteip.com/8p3/GetProjectID.aspx',
        'drawings' => 'http://portal.liteip.com/8p3/GetDrawingID.aspx?ProjectID=%d',
        'devices' => 'http://portal.liteip.com/8p3/GetDevice.aspx?DrawingID=%d&E3Only=%d'
    );

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;


    /**
     * @return array|object|Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }


    /**
     * @return \Application\Service\GoogleService
     */
    public function getGoogleService()
    {
        $config = $this->getServiceLocator()->get('Config');
        $user = new User();
        $user->setEmail('crm@8point3led.co.uk');
        $user->setForename('Emergency Alerts');
        $user->setGoogleEnabled(false);

        return new \Application\Service\GoogleService($config['openAuth2']['google'], $user, $this->getEntityManager());
    }

    /**
     * @param $message
     * @param bool|true $verbose
     */
    public function addOutputMessage($message, $verbose = true)
    {
        if ($verbose === false) {
            return;
        }

        echo date('Y-m-d H:i:s ') . $message . self::$NEWLINE;

    }

    /**
     * @param $uri
     * @return \Zend\Http\Response
     */
    public function curlRequest($uri)
    {
        $config = array(
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
        );
        $client = new Client($uri, $config);
        $client->setMethod('get');

        return $client->send();
    }

    /**
     * @param $status
     * @return string
     */
    public function getDeviceStatusText($status)
    {
        switch ($status) {
            case 0:
                return 'Status Unknown';
            case 4:
                return 'Lamp Fault';
            case 5:
                return 'Charge Fault';
            case 6:
                return 'Battery Fault';
            case 7:
                return 'Fault- Unspecified';
            default:
                return 'No fault';
        }
    }

    /**
     * get LiteIp Service
     * @return \Application\Service\LiteIpService
     */
    public function getLiteIpService() {
        return $this->getServiceLocator()->get('LiteIpService');
    }


    public function synchronizeLiteipAction() {
        date_default_timezone_set('Europe/London');
        // Check command flags
        $request = $this->getRequest();
        $mode = $request->getParam('mode', 'all'); // defaults to 'all'
        $verbose = $request->getParam('verbose') || $request->getParam('v');

        switch ($mode) {
            case 'rbs':
                $customerGroup = 19;
                break;
            case 'non-rbs':
                $customerGroup = 10;
                break;
            default:
                throw new \Exception('Illegal mode selected');
                break;
        }
        $this->addOutputMessage("Starting {$mode} synchronization");

        $liteIPService = $this->getLiteIpService();

        $this->addOutputMessage('Synchronizing projects data', $verbose);
        $liteIPService->synchronizeProjectsData(true);
        $this->addOutputMessage('Synchronizing drawings data', $verbose);
        $liteIPService->synchronizeDrawingsData();

        $em = $this->getEntityManager();

        // get projects data for grouping
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('Application\Entity\LiteipProject', 'p')
            ->where('p.CustomerGroup=?1')
            ->andWhere('p.TestSite=false')
            ->setParameter(1, $customerGroup);
        $projects = $qb->getQuery()->getResult();

        foreach ($projects as $project) {
            $this->addOutputMessage('Synchronizing: ' . (empty($project->getProjectDescription()) ? 'undefined' : $project->getProjectDescription()) . ' (' . $project->getProjectID() . ' - ' . $project->getPostCode() . ')', $verbose);
            $liteIPService->synchronizeDevicesData(false, $project->getProjectID());
        }

        $this->addOutputMessage("synchronization complete");

        return;
    }

    public function emergencyAction()
    {
        date_default_timezone_set('Europe/London');
        $em = $this->getEntityManager();
        $liteIPService = $this->getLiteIpService();

        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $alerts = array();

        // Check command flags
        $mode = $request->getParam('mode', 'all'); // defaults to 'all'
        $verbose = $request->getParam('verbose') || $request->getParam('v');
        $synchronize = $request->getParam('synchronize') || $request->getParam('s');
        $testMode = $request->getParam('test') || $request->getParam('t');
        $flush = true;

        switch (strtolower($mode)) {
            case 'rbs':
                $subject = 'EMERGENCY REPORT ALERT: RBS';
                $to = array('rbsemergency@8point3led.co.uk');
                $customerGroup = 19;
                break;
            case 'non-rbs':
                $subject = 'EMERGENCY REPORT ALERT: 8POINT3';
                $to = array('emergency@8point3led.co.uk');
                $customerGroup = 10;
                break;
            default:
                throw new \Exception('Illegal mode selected');
                break;
        }

        // configure test mode
        if ($testMode) {
            $to = array('jonny.p.cook@8point3led.co.uk');
        }

        // get projects data for grouping
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('Application\Entity\LiteipProject', 'p')
            ->where('p.CustomerGroup=?1')
            ->andWhere('p.TestSite=false')
            ->setParameter(1, $customerGroup);
        $projects = $qb->getQuery()->getResult();


        // synchronize project device data
        if ($synchronize) {
            $this->addOutputMessage('Starting synchronization of projects ...', $verbose);

            foreach ($projects as $project) {
                $this->addOutputMessage(sprintf('Synchronizing project %s (id=%s)', $project->getProjectDescription(), $project->getProjectID()), $verbose);
                $liteIPService->synchronizeDevicesData(false, $project->getProjectID());
            }
            $this->addOutputMessage('Synchronization of projects completed', $verbose);
        } else {
            $this->addOutputMessage('Skipping synchronization of projects', $verbose);
        }

        $this->addOutputMessage('Generating report', $verbose);

        // run reports against customer group
        $errorCount = 0;
        $warningCount = 0;
        $sitesPolled = 0;
        $devicesPolled = 0;
        foreach ($projects as $project) {
            $sitesPolled++;
            $now = new \DateTime('now');
            $qb = $em->createQueryBuilder();
            $qb->select('d')
                ->from('Application\Entity\LiteipDevice', 'd')
                ->innerJoin('d.drawing', 'dr')
                ->where('dr.project=?1')
                ->andWhere('d.IsE3=true')
                ->setParameter(1, $project->getProjectID());
            $this->addOutputMessage(sprintf('Synchronizing project data for: %s (id=%s)', $project->getProjectDescription(), $project->getProjectID()) , $verbose);

            $devices = $qb->getQuery()->getResult();
            foreach ($devices as $device) {
                $devicesPolled++;
                if ($device->getStatus()->isFault()) {
                    $this->addError($alerts, $device, $device->getDrawing(), $device->getDrawing()->getProject());
                    $errorCount++;
                    $this->addOutputMessage(sprintf('Error! device %d %s', $device->getDeviceID(), $device->getStatus()->getDescription()), $verbose);
                }

                $timestamp = empty($device->getLastE3StatusDate()) ? 0 : $device->getLastE3StatusDate()->getTimestamp();
                $diff = $now->getTimestamp() - $timestamp;
                if(floor($diff / (60 * 60 * 24)) > 0) { // if not tested for 24 hours
                    $this->addWarning($alerts, floor($diff / (60 * 60 * 24)) . ' days untested', $device, $device->getDrawing(), $device->getDrawing()->getProject());
                    $warningCount++;
                    $this->addOutputMessage(sprintf('Warning! device %d untested in %d days', $device->getDeviceID(), floor($diff / (60 * 60 * 24))), $verbose);
                }
            }
        }

        // build email
        $this->addOutputMessage('Sending report', $verbose);
        if (($errorCount > 0) || ($warningCount > 0)) {
            $html = '<style>th{text-align: left;} th,td{padding: 2px}</style>' .
                '<h3>Report Summary</h3><table><tbody>' .
                '<tr><th>Sites polled</th><td>%d</td></tr>' .
                '<tr><th>Devices polled</th><td>%d</td></tr>' .
                '<tr><th>Number of errors</th><td>%d</td></tr>' .
                '<tr><th>Number of warnings</th><td>%d</td></tr>' .
                '</tbody></table>' .
                '<br>%s<br>%s<p>Note: Position relates to the drawing (top, left)</p>' .
                '<p>Please do not reply to this message. Replies to this message are routed to an unmonitored mailbox.</p>';
            $tblErrors = '';
            if ($errorCount > 0) {
                foreach ($alerts as $project) {
                    $postcode = str_replace('_', ' ', $project['project']->getPostCode());
                    foreach ($project['drawings'] as $drawings) {
                        $drawingName = preg_replace('/[.][^.]+$/', '', $drawings['drawing']->getDrawing());
                        if (!empty($drawings['errors'])) {
                            foreach ($drawings['errors'] as $error) {
                                $tblErrors .= '<tr><td>' . $project['project']->getProjectDescription() . '</td><td>' . $postcode . '</td><td>' . $drawingName . '</td><td>' . implode('</td><td>', $error) . '</td></tr>';
                            }
                        }
                    }
                }
                $tblErrors = '<h3>Error Report</h3><table><thead><tr><th>Project</th><th>Postcode</th><th>Drawing</th><th>Device Id</th><th>Device SN</th><th>Status</th><th>Last Tested</th><th>Position</th></tr></thead><tbody>' . $tblErrors . '</tbody></table>';
            }
            $tblWarnings = '';
            if ($warningCount > 0) {
                foreach ($alerts as $project) {
                    $postcode = str_replace('_', ' ', $project['project']->getPostCode());
                    foreach ($project['drawings'] as $drawings) {
                        $drawingName = preg_replace('/[.][^.]+$/', '', $drawings['drawing']->getDrawing());
                        if (!empty($drawings['warnings'])) {
                            foreach ($drawings['warnings'] as $warning) {
                                $tblWarnings .= '<tr><td>' . $project['project']->getProjectDescription() . '</td><td>' . $postcode . '</td><td>' . $drawingName . '</td><td>' . implode('</td><td>', $warning) . '</td></tr>';
                            }
                        }
                    }
                }
                $tblWarnings = '<h3>Warnings Report</h3><table><thead><tr><th>Project</th><th>Postcode</th><th>Drawing</th><th>Device Id</th><th>Device SN</th><th>Status</th><th>Last Tested</th><th>Position</th></tr></thead><tbody>' . $tblWarnings . '</tbody></table>';
            }

            // send email
            $this->getGoogleService()->sendGmail($subject, sprintf($html, $sitesPolled, $devicesPolled, $errorCount, $warningCount, $tblErrors, $tblWarnings), $to);
            $this->addOutputMessage('Email sent', true);
        }

        $this->addOutputMessage($errorCount . ' errors found');
        $this->addOutputMessage($warningCount . ' warnings found');

        return;
    }

    /**
     * @param array $alerts
     * @param $device
     * @param $drawing
     * @param $project
     */
    function addError (array &$alerts, $device, $drawing, $project) {
        $this->prepareAlert($alerts, $drawing, $project);
        $alerts[$project->getProjectID()]['drawings'][$drawing->getDrawingID()]['errors'][] = array(
            $device->getDeviceID(),
            $device->getDeviceSN(),
            $device->getStatus()->getDescription(),
            empty($device->getLastE3StatusDate()) ? '' : $device->getLastE3StatusDate()->format('d/m/Y H:i:s'),
            '(' . $device->getPosTop() . ',' . $device->getPosLeft()  . ')'
        );
    }

    /**
     * @param array $alerts
     * @param $type
     * @param $device
     * @param $drawing
     * @param $project
     */
    function addWarning (array &$alerts, $type, $device, $drawing, $project) {
        $this->prepareAlert($alerts, $drawing, $project);
        $alerts[$project->getProjectID()]['drawings'][$drawing->getDrawingID()]['warnings'][] = array(
            $device->getDeviceID(),
            $device->getDeviceSN(),
            $type,
            empty($device->getLastE3StatusDate()) ? '' : $device->getLastE3StatusDate()->format('d/m/Y H:i:s'),
            '(' . $device->getPosTop() . ',' . $device->getPosLeft()  . ')'
        );
    }

    /**
     * @param array $alerts
     * @param $drawing
     * @param $project
     */
    function prepareAlert (array &$alerts, $drawing, $project) {
        if (empty($alerts[$project->getProjectID()])) {
            $alerts[$project->getProjectID()] = array(
                'project' => $project,
                'drawings' => array(
                    $drawing->getDrawingID() => array(
                        'drawing' => $drawing,
                        'errors' => array(),
                        'warnings' => array()
                    )
                )
            );
        } elseif (empty($alerts[$project->getProjectID()]['drawings'][$drawing->getDrawingID()])) {
            $alerts[$project->getProjectID()]['drawings'][$drawing->getDrawingID()] = array(
                'drawing' => $drawing,
                'errors' => array(),
                'warnings' => array()
            );
        }
    }


}
