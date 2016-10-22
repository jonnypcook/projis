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


class SearchController extends AuthController
{
    
    public function indexAction()
    {
        $this->setCaption('Search');
        
        $term = $this->params()->fromQuery('searchfull', false);
        if (!empty($term)) {
            $term = trim($term);
        }
        $em = $this->getEntityManager();
        $matches = array();
        if (preg_match('/^([\d]+)([-]([\d]+))?$/', $term, $matches)) {
            // need to check for project
            $cid = (int)$matches[1];
            if (!empty($matches[3]))  { // project lookup
                $pid = (int)$matches[3];
                $project = $em->getRepository('Project\Entity\Project')->findByProjectId($pid, array('client_id'=>$cid));
                if ($project instanceof \Project\Entity\Project) {
                    return $this->redirect()->toRoute('project', array('cid'=>$cid, 'pid'=>$pid));
                }
            }
            
            $client = $em->find('Client\Entity\Client', $cid);
            if (!empty($client)) {
                return $this->redirect()->toRoute('client', array('id'=>$cid));
            }
            
        }
        
        $projects = array();
        
        // client search 
        if (!empty($term)){
            $projects = $em->getRepository('Project\Entity\Project')->searchByName($term, array('array'=>array(
                'p.name AS pName',
                'c.name AS cName',
                's.name AS sName',
                's.weighting',
                's.job',
                's.halt',
                'c.clientId',
                'p.projectId',
                't.typeId',
                'p.cancelled',
                'p.test',
            )));
            
            
            //$this->debug()->dump($projects);
        }
        
        $this->getView()
                ->setVariable('count', count($projects))
                ->setVariable('term', $term)
                ->setVariable('projects', $projects);

        return $this->getView();
    }
    
     
}
