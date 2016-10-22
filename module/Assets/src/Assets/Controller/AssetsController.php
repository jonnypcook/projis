<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Assets\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User,    Application\Entity\Address,    Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class AssetsController extends \Application\Controller\AuthController
{
    
    public function indexAction()
    {
        $this->setCaption('Asset Tracking');
        /*$this->getView()
                ->setVariable('projects', $projects)
                ->setVariable('info', $info)
                ->setVariable('activities', $activities)
                ->setVariable('user', $this->getUser())
                ->setVariable('formActivity', $formActivity)
                ->setVariable('formCalendarEvent', $formCalendarEvent)
                ->setVariable('formRemotePhosphor', $formRemotePhosphor)
                ;/**/
        
        return $this->getView();
    }
    
    
    public function installersAction()
    {
        $this->setCaption('Installers\' Barcode Sheets');
        /*$this->getView()
                ->setVariable('projects', $projects)
                ->setVariable('info', $info)
                ->setVariable('activities', $activities)
                ->setVariable('user', $this->getUser())
                ->setVariable('formActivity', $formActivity)
                ->setVariable('formCalendarEvent', $formCalendarEvent)
                ->setVariable('formRemotePhosphor', $formRemotePhosphor)
                ;/**/
        
        return $this->getView();
    }
    
    public function findAction() {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            $spaceId = $this->params()->fromPost('spaceId', false);
            
            if (empty($spaceId)) {
                throw new \Exception('space identifier not found');
            }
            
            if (!preg_match('/^[\d]+$/',$spaceId)) {
                throw new \Exception('space identifier invalid');
            }
            
            $space = $this->getEntityManager()->find('Space\Entity\Space', $spaceId);
            if (empty($space)) {
                throw new \Exception('space could not be found');
            } 
            
            // find products
            $systems = $this->getEntityManager()->getRepository('Space\Entity\System')->findBySpaceId($spaceId, array('array'=>true, 'type'=>1));
            $fields = array(
                'systemId'=>true,
                'model'=>true,
            );
            foreach ($systems as $idx=>$system) {
                $systems[$idx] = array_intersect_key($system, $fields);
            }
            
            $data = array('err'=>false, 'space'=>array (
                'id'=>$space->getSpaceId(),
                'name'=>$space->getName(),
                'systems'=> $systems,
                'building'=>array (
                    'name'=>($space->getRoot()?'Root':empty($space->getBuilding()->getName())?'un-named':$space->getBuilding()->getName()),
                    'id'=>$space->getBuilding()->getBuildingId(),
                ),
                'project'=>array(
                    'name'=>$space->getProject()->getName(),
                    'id'=>$space->getProject()->getProjectId(),
                    'client'=>array(
                        'name'=>$space->getProject()->getClient()->getName(),
                        'id'=>$space->getProject()->getClient()->getClientId(),
                    )
                ),
                
            ));
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    public function scanAction() {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            $em = $this->getEntityManager();
            $spaceId = $this->params()->fromPost('spaceId', false);
            $serialId = $this->params()->fromPost('serialId', false);
            $systemId = $this->params()->fromPost('systemId', false);
            
            if (empty($spaceId)) {
                throw new \Exception('space identifier not found');
            }
            
            if (!preg_match('/^[\d]+$/',$spaceId)) {
                throw new \Exception('space identifier invalid');
            }
            
            $space = $em->find('Space\Entity\Space', $spaceId);
            if (empty($space)) {
                throw new \Exception('space could not be found');
            } 
            
            if (empty($serialId)) {
                throw new \Exception('serial identifier not found');
            }
            
            if (!preg_match('/^[\d]+$/',$serialId)) {
                throw new \Exception('serial identifier invalid');
            }
            
            // check if serial number is already assigned
            $query = $em->createQuery('SELECT count(s) FROM Job\Entity\Serial s WHERE s.serialId ='.$serialId);
            $serialCount = $query->getSingleScalarResult();
            if ($serialCount>0) {
                throw new \Exception('The specified serial has already been assigned');
            }
            
            // build serial entry
            $serial = new \Job\Entity\Serial();
            $serial
                    ->setSerialId($serialId)
                    ->setSpace($space)
                    ->setProject($space->getProject());
            
            
            if (!empty($systemId)) {
                $system = $em->find('Space\Entity\System', $systemId);
                if (empty($system)) {
                    throw new \Exception('System does not exist');
                }
                if ($system->getSpace()->getSpaceId()!=$spaceId) {
                    throw new \Exception('System does not belong to space');
                }
                $serial->setSystem($system);
            }
            
            $em->persist($serial);
            $em->flush();
            $data = array('err'=>false, 'serialId'=>$serialId);
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
}
