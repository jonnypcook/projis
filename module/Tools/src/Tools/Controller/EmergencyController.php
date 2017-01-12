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
     * @param $response
     * @param $message
     * @param bool|true $verbose
     * @param bool|false $flush
     */
    public function addOutputMessage(&$response, $message, $verbose = true, $flush = false)
    {
        if ($verbose === false) {
            return;
        }

        $response .= date('Y-m-d H:i:s ') . $message . self::$NEWLINE;

        if ($flush === true) {
            echo $response;
            $response = '';
        }
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

        $consoleResponse = "";
        $request = $this->getRequest();
        $errorCount = 0;
        $warningCount = 0;
        $alerts = array();
        $excludes = array(177, 187);

        // Check mode
        $mode = $request->getParam('mode', 'all'); // defaults to 'all'

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
        // Check verbose flag
        $verbose = $request->getParam('verbose') || $request->getParam('v');
        $flush = true;
        $synchronize = $request->getParam('synchronize') || $request->getParam('s');

        // get projects data for grouping
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('Application\Entity\LiteipProject', 'p')
            ->where('p.CustomerGroup=?1')
            ->setParameter(1, $customerGroup);
        $projects = $qb->getQuery()->getResult();


        // synchronize project device data
        if ($synchronize) {
            $this->addOutputMessage($consoleResponse, 'Starting synchronization of projects ...', $verbose, $flush);

            foreach ($projects as $project) {
                $this->addOutputMessage($consoleResponse, sprintf('Synchronizing project %s (id=%s)', $project->getProjectDescription(), $project->getProjectID()), $verbose, $flush);
                $liteIPService->synchronizeDevicesData(false, $project->getProjectID());
            }
            $this->addOutputMessage($consoleResponse, 'Synchronization of projects completed', $verbose, $flush);
        } else {
            $this->addOutputMessage($consoleResponse, 'Skipping synchronization of projects', $verbose, $flush);
        }

        $this->addOutputMessage($consoleResponse, 'Generating report', $verbose, $flush);

        // run reports against customer group
        foreach ($projects as $project) {
            $now = new \DateTime('now');
            $qb = $em->createQueryBuilder();
            $qb->select('d')
                ->from('Application\Entity\LiteipDevice', 'd')
                ->innerJoin('d.drawing', 'dr')
                ->where('dr.project=?1')
                ->andWhere('d.IsE3=true')
                ->setParameter(1, $project->getProjectID());
            $this->addOutputMessage($consoleResponse, sprintf('Synchronizing project data for: %s (id=%s)', $project->getProjectDescription(), $project->getProjectID()) , $verbose, $flush);

            $devices = $qb->getQuery()->getResult();
            foreach ($devices as $device) {
                if ($device->getStatus()->isFault()) {
                    $this->addError($alerts, $device, $device->getDrawing(), $device->getDrawing()->getProject());
                    $errorCount++;
                    $this->addOutputMessage($consoleResponse, sprintf('Error! device %d %s', $device->getDeviceID(), $device->getStatus()->getDescription()), $verbose, $flush);
                }

                $timestamp = empty($device->getLastE3StatusDate()) ? 0 : $device->getLastE3StatusDate()->getTimestamp();
                $diff = $now->getTimestamp() - $timestamp;
                if(floor($diff / (60 * 60 * 24)) > 0) { // if not tested for 24 hours
                    $this->addWarning($alerts, floor($diff / (60 * 60 * 24)) . ' days untested', $device, $device->getDrawing(), $device->getDrawing()->getProject());
                    $warningCount++;
                    $this->addOutputMessage($consoleResponse, sprintf('Warning! device %d untested in %d days', $device->getDeviceID(), floor($diff / (60 * 60 * 24))), $verbose, $flush);
                }
            }
        }

        // build email
        $this->addOutputMessage($consoleResponse, 'Sending report', $verbose, $flush);
        if (($errorCount > 0) || ($warningCount > 0)) {
            $html = '<style>th{text-align: left;} th,td{padding: 2px}</style><p>There are %d errors.<br>There are %d warnings.</p>%s<br>%s<p>Note: Position relates to the drawing (top, left)</p><p>Please do not reply to this message. Replies to this message are routed to an unmonitored mailbox.</p>';
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
            $to = array('jonny.p.cook@8point3led.co.uk');
            $this->getGoogleService()->sendGmail($subject, sprintf($html, $errorCount, $warningCount, $tblErrors, $tblWarnings), $to);
            $this->getGoogleService()->sendGmail('Test Subject', 'Test Email', array('jonny.p.cook@8point3led.co.uk'));
            $this->addOutputMessage($consoleResponse, 'Email sent', true);
        }

        $this->addOutputMessage($consoleResponse, $errorCount . ' errors found');
        $this->addOutputMessage($consoleResponse, $warningCount . ' warnings found');

        return $consoleResponse;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function emergency2Action()
    {


        $this->addOutputMessage($consoleResponse, 'starting output', $verbose);

        $uri = self::$URIS['projects'];
        $response = $this->curlRequest($uri);

        if ($response->getStatusCode() === 200) {
            $projects = json_decode($response->getBody());
            foreach ($projects as $project) {
                if (empty ($project->ProjectID) || in_array($project->ProjectID, $excludes)) { //ignore test projects
                    $this->addOutputMessage($consoleResponse, sprintf('Skipping project %s', $project->ProjectDescription), $verbose);
                    continue;
                }

                if ($projectMatch !== false && !preg_match($projectMatch, $project->ProjectDescription)) {
                    $this->addOutputMessage($consoleResponse, sprintf('Ignoring project #%d (%s)', $project->ProjectID, $project->ProjectDescription), $verbose);
                    continue;
                }

                $uri = sprintf(self::$URIS['drawings'], $project->ProjectID);
                $this->addOutputMessage($consoleResponse, sprintf('Gettings drawings for project #%d (%s)', $project->ProjectID, $project->ProjectDescription), $verbose);

                $response = $this->curlRequest($uri);
                if ($response->getStatusCode() !== 200) {
                    continue;
                }

                $drawings = json_decode($response->getBody());

                if (empty($drawings)) {
                    $this->addOutputMessage($consoleResponse, sprintf('No drawings found for project #%d - skipping', $project->ProjectID), $verbose);
                    continue;
                }

                foreach ($drawings as $drawing) {
                    if (empty ($drawing->DrawingID)) {
                        continue;
                    }

                    $uri = sprintf(self::$URIS['devices'], $drawing->DrawingID, 1);
                    $this->addOutputMessage($consoleResponse, sprintf('Gettings devices for drawing #%d in project #%d', $drawing->DrawingID, $project->ProjectID), $verbose);

                    $response = $this->curlRequest($uri);
                    if ($response->getStatusCode() !== 200) {
                        continue;
                    }

                    $devices = json_decode($response->getBody());

                    if (empty($devices)) {
                        $this->addOutputMessage($consoleResponse, sprintf('No devices found for drawing #%d - skipping', $drawing->DrawingID), $verbose);
                        continue;
                    }

                    $now = new \DateTime('now');

                    foreach ($devices as $device) {
                        switch ($device->LastE3Status) {
                            case -1:
                            case 1:
                            case 2:
                            case 3:
                                $fault = false;
                                break;
                            default:
                                $fault = true;
                        }

                        $date = \DateTime::createFromFormat('d/m/Y H:i:s', $device->LastE3StatusDate);
                        if ($fault === true) {
                            $this->addError($alerts, $device, $drawing, $project);
                            $errorCount++;
                            $this->addOutputMessage($consoleResponse, sprintf('Error! device %d %s', $device->DeviceID, $this->getDeviceStatusText($device->LastE3Status)), $verbose);
                        }

                        $diff = $now->getTimestamp() - $date->getTimestamp();
                        if(floor($diff / (60 * 60 * 24)) > 0) { // if not tested for 24 hours
                            $this->addWarning($alerts, floor($diff / (60 * 60 * 24)) . ' days untested', $device, $drawing, $project);
                            $warningCount++;
                            $this->addOutputMessage($consoleResponse, sprintf('Warning! device %d untested in %d days', $device->DeviceID, floor($diff / (60 * 60 * 24))), $verbose);
                        }
                    }

                }

            }

            // build email
            if (($errorCount > 0) || ($warningCount > 0)) {
                $html = '<style>th{text-align: left;} th,td{padding: 2px}</style><p>There are %d errors.<br>There are %d warnings.</p>%s<br>%s<p>Note: Position relates to the drawing (top, left)</p><p>Please do not reply to this message. Replies to this message are routed to an unmonitored mailbox.</p>';
                $tblErrors = '';
                if ($errorCount > 0) {
                    foreach ($alerts as $project) {
                        $postcode = str_replace('_', ' ', $project['project']->PostCode);
                        foreach ($project['drawings'] as $drawings) {
                            $drawingName = preg_replace('/[.][^.]+$/', '', $drawings['drawing']->Drawing);
                            if (!empty($drawings['errors'])) {
                                foreach ($drawings['errors'] as $error) {
                                    $tblErrors .= '<tr><td>' . $project['project']->ProjectDescription . '</td><td>' . $postcode . '</td><td>' . $drawingName . '</td><td>' . implode('</td><td>', $error) . '</td></tr>';
                                }
                            }
                        }
                    }
                    $tblErrors = '<h3>Error Report</h3><table><thead><tr><th>Project</th><th>Postcode</th><th>Drawing</th><th>Device Id</th><th>Device SN</th><th>Status</th><th>Last Tested</th><th>Position</th></tr></thead><tbody>' . $tblErrors . '</tbody></table>';
                }
                $tblWarnings = '';
                if ($warningCount > 0) {
                    foreach ($alerts as $project) {
                        $postcode = str_replace('_', ' ', $project['project']->PostCode);
                        foreach ($project['drawings'] as $drawings) {
                            $drawingName = preg_replace('/[.][^.]+$/', '', $drawings['drawing']->Drawing);
                            if (!empty($drawings['warnings'])) {
                                foreach ($drawings['warnings'] as $warning) {
                                    $tblWarnings .= '<tr><td>' . $project['project']->ProjectDescription . '</td><td>' . $postcode . '</td><td>' . $drawingName . '</td><td>' . implode('</td><td>', $warning) . '</td></tr>';
                                }
                            }
                        }
                    }
                    $tblWarnings = '<h3>Warnings Report</h3><table><thead><tr><th>Project</th><th>Postcode</th><th>Drawing</th><th>Device Id</th><th>Device SN</th><th>Status</th><th>Last Tested</th><th>Position</th></tr></thead><tbody>' . $tblWarnings . '</tbody></table>';
                }

                // send email
                $this->getGoogleService()->sendGmail($subject, sprintf($html, $errorCount, $warningCount, $tblErrors, $tblWarnings), $to);
                $this->addOutputMessage($consoleResponse, 'Email sent', true);
            }

            $this->addOutputMessage($consoleResponse, $errorCount . ' errors found');
            $this->addOutputMessage($consoleResponse, $warningCount . ' warnings found');
        } else {
            $this->addOutputMessage($consoleResponse, 'failed to make curl request');
        }

        $this->addOutputMessage($consoleResponse, 'completed');
        return $consoleResponse;
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
