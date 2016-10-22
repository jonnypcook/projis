<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User,    Application\Entity\Address,    Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class DashboardController extends AuthController
{
    
    public function mailAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal message');
            }
            
            $preview = $this->params()->fromPost('preview', false);
            
            $googleService = $this->getGoogleService();
            
            if (!$googleService->hasGoogle()) {
                throw new \Exception ('the service is not enabled for this user');
            }
            
            $data = $googleService->findGmailThreads(array (
                'unread'=>true,
                'in'=>'inbox',
                'refresh'=>!empty($preview),
                'maxResults'=>5
            ), true, empty($preview));
            
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    public function test1Action() { 
        
        die('stop');
    }
    
    /**
     * main Dashboard action
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $info = array();

        // calculate monthly conversion
        $tmFrom = new \DateTime(date('Y-m-d H:i:s', mktime(0,0,0,date('m'),1,date('Y'))));
        $tmTo = new \DateTime(date('Y-m-d H:i:s', mktime(0,0,0,date('m')+1,1,date('Y'))));
        $dql = 'SELECT SUM(sys.ppu * sys.quantity) '
                . 'FROM Space\Entity\System sys '
                . 'JOIN sys.space s '
                . 'JOIN s.project p '
                . 'JOIN p.client c '
                . 'WHERE c.user = :uid AND '
                . 'p.contracted IS NOT NULL AND '
                . 'p.contracted>=\''.$tmFrom->format('Y-m-d H:i:s').'\' AND '
                . 'p.contracted<\''.$tmTo->format('Y-m-d H:i:s').'\'';
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameters(array('uid' => $this->getUser()->getUserId()));
        $info['monthlySales'] = $q->getSingleScalarResult();
        

        // find the number of active projects that a user has
        $dql = 'SELECT COUNT(p) FROM Project\Entity\Project p JOIN p.client c JOIN p.status s WHERE c.user = :uid AND s.job=0 AND s.halt=0';
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameters(array('uid' => $this->getUser()->getUserId()));

        $info['activeProjects'] = $q->getSingleScalarResult();
        
        // find the number of active jobs that a user has
        $dql = 'SELECT COUNT(p) FROM Project\Entity\Project p JOIN p.client c JOIN p.status s WHERE c.user = :uid AND s.job=1 AND s.halt=0';
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameters(array('uid' => $this->getUser()->getUserId()));

        $info['activeJobs'] = $q->getSingleScalarResult();
        
        // find the number of cancelled projects that a user has
        $dql = 'SELECT COUNT(c) FROM Contact\Entity\Contact c JOIN c.client cl JOIN cl.user u WHERE u.company = :company';
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameters(array('company' => $this->getUser()->getCompany()->getCompanyId()));

        $info['companyContacts'] = $q->getSingleScalarResult();
        
        // find the number of clients that a user has
        $dql = 'SELECT COUNT(c) FROM Client\Entity\Client c WHERE c.user = :uid';
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameters(array('uid' => $this->getUser()->getUserId()));

        $info['activeClients'] = $q->getSingleScalarResult();
        

        // find the number of cancelled projects that a user has
        $tm = mktime(0,0,0,date('m'), date('d')-14, date('Y'));
        $dql = 'SELECT COUNT(a) FROM Application\Entity\Activity a WHERE a.user = :uid AND a.startDt>=\''.date('Y-m-d H:i:s',$tm).'\'';
        $q = $this->getEntityManager()->createQuery($dql);
        $q->setParameters(array('uid' => $this->getUser()->getUserId()));

        
        // find last 3 projects modified
        $projects = $this->getEntityManager()->getRepository('Project\Entity\Project')->findByUserId($this->getUser()->getUserId(), false, array(
            'max' => 3,
            'auto'=> true,
        ));

        $info['activityCount'] = $q->getSingleScalarResult();
        
        $activities = $this->getEntityManager()->getRepository('Application\Entity\Activity')->findByUserId($this->getUser()->getUserId(), true, array(
            'max' => 8,
            'auto'=> true,
            'project' => true,
        ));

        $formActivity = new \Application\Form\ActivityAddForm($this->getEntityManager(), array());
        
        $formActivity
                ->setAttribute('action', '/activity/add/')
                ->setAttribute('class', 'form-nomargin');
        
        $formCalendarEvent = new \Application\Form\CalendarEventAddForm();
        $formCalendarEvent 
                ->setAttribute('action', '/calendar/addevent/')
                ->setAttribute('class', 'form-nomargin');
        
        $formRemotePhosphor = new \Application\Form\RemotePhosphorForm($this->getEntityManager());
        $formRemotePhosphor
                ->setAttribute('action', '/tools/rpQuickCalculate/')
                ->setAttribute('class', 'form-horizontal');

        $this->getView()
                ->setVariable('projects', $projects)
                ->setVariable('info', $info)
                ->setVariable('activities', $activities)
                ->setVariable('user', $this->getUser())
                ->setVariable('formActivity', $formActivity)
                ->setVariable('formCalendarEvent', $formCalendarEvent)
                ->setVariable('formRemotePhosphor', $formRemotePhosphor)
                ;
        
        return $this->getView();
    }
    
    
    
}
