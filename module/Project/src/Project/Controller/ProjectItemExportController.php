<?php
namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Entity\User; 
use Application\Controller\AuthController;

use Project\Form\SetupForm;
use Space\Form\SpaceCreateForm;

use Zend\Mvc\MvcEvent;

use Zend\View\Model\JsonModel;

use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;

use DOMPDFModule\View\Model\PdfModel;

use Project\Service\DocumentService;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ProjectitemexportController extends ProjectSpecificController
{
    
    /*public function __construct(DocumentService $ds) {
        parent::__construct();
        $this->setDocumentService($ds);
    }/**/

    public function onDispatch(MvcEvent $e) {
        $this->ignoreStatusRedirects = true;
        return parent::onDispatch($e);
    }
    
    public function indexAction()
    {
        $this->setCaption('Project Export Wizard');
        
        $breakdown = $this->getModelService()->spaceBreakdown($this->getProject());
        //$this->debug()->dump($breakdown);
        $form = new \Project\Form\ExportProjectForm();
        $form
            ->setAttribute('action', '/client-'.$this->getProject()->getClient()->getClientId().'/project-'.$this->getProject()->getProjectId().'/export/createproject/')
            ->setAttribute('class', 'form-horizontal');
        $this->getView()
                ->setVariable('form', $form)
                ->setVariable('breakdown', $breakdown);/**/
        
		return $this->getView();
    }
    
    
    public function createProjectAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request');
            }
            
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('illegal method');
            }
                
            $form = new \Project\Form\ExportProjectForm();
            $form->setInputFilter(new \Project\Filter\ExportProjectFilter());
            $post = $this->params()->fromPost();
            $form->setData($post);
            
            
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                // check for duplication
                $query = $em->createQuery('SELECT count(p) FROM Project\Entity\Project p WHERE p.client='.$this->getProject()->getClient()->getClientId().' AND p.type IN (1,2) AND p.name=?1')->setParameter(1, $form->get('name')->getValue());
                $count = $query->getSingleScalarResult();
                if ($count) {
                    $data = array('err'=>true, 'info'=>array('name'=>array('alreadyexists'=>'Project name already exists')));
                } else {
                    $products = array();
                    $project = new \Project\Entity\Project();
                    $info = $this->getProject()->getArrayCopy();
                    $status = $this->getEntityManager()->find('Project\Entity\Status', 1);
                    
                    unset($info['projectId']);
                    unset($info['states']);
                    unset($info['serials']);
                    unset($info['properties']);
                    $project->populate($info);
                    $project
                        ->setName($form->get('name')->getValue())
                        ->setFinanceProvider(null);
                    
                    $project->setStatus($status);
                    
                    $em->persist($project);
                    
                    if (!empty($post['systemId'])) {
                        $spaces = array();
                        $systems = array();
                        $spaceObjs = array();
                        $systemObjs = array();
                        foreach ($post['systemId'] as $system) {
                            if (preg_match('/^([\d]+)[_]([\d]+)$/', $system, $matches)) {
                                $spaces[$matches[1]] = $matches[1];
                                $systems[$matches[2]] = $matches[2];
                            }
                        }
                        
                        
                        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
                        $queryBuilder
                                ->select('s.name, s.notes, s.root, s.created, s.spaceId, b.buildingId AS building')
                                ->from('Space\Entity\Space', 's')
                                ->leftJoin('s.building', 'b')
                                ->where('s.spaceId IN ('.implode(',',$spaces).')')
                                ->andWhere('s.project=?1')
                                ->setParameter(1, $this->getProject()->getProjectId());
                        
                        $query = $queryBuilder->getQuery();
                        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

                        foreach ($result as $spaceData) {
                            $space = new \Space\Entity\Space();
                            $spaceId = $spaceData['spaceId'];
                            unset($spaceData['spaceId']);
                            $hydrator = new DoctrineHydrator($em,'Space\Entity\Space');
                            $hydrator->hydrate($spaceData, $space);
                            $space->setProject($project);
                            $em->persist($space);
                            $spaceObjs[$spaceId] = $space;
                        }/**/
                        
                        //$this->debug()->dump($result);
                        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
                        $queryBuilder
                            ->select('sp.spaceId, p.productId AS product, l.legacyId AS legacy, s.cpu, s.ppu, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.label, s.locked, s.attributes')
                            ->from('Space\Entity\System', 's')
                            ->join('s.space', 'sp')
                            ->join('s.product', 'p')
                            ->leftjoin('s.legacy', 'l')
                            ->where('sp.project=?1')
                            ->andWhere('s.systemId IN ('.implode(',',$systems).')')
                            ->setParameter(1, $this->getProject()->getProjectId())
                            ;
        
                        $query = $queryBuilder->getQuery();
                        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
                        //$this->debug()->dump($result);
                        
                        foreach ($result as $systemData) {
                            $products[$systemData['product']] = $systemData['product'];
                            $system = new \Space\Entity\System();
                            $system->setSpace($spaceObjs[$systemData['spaceId']]);
                            unset($systemData['spaceId']);
                            $hydrator = new DoctrineHydrator($em,'Space\Entity\System');
                            $hydrator->hydrate($systemData, $system);
                            $em->persist($system);
                        }/**/
                        
                    }
                    
                    $em->flush();
                    
                    if (!empty($spaceObjs)) {
                        foreach ($spaceObjs as $space) {
                            $this->synchroniseInstallation($space);
                        }
                    }
                    
                    $data = array('err'=>false, 'url'=>'/client-'.$project->getClient()->getClientId().'/project-'.$project->getProjectId().'/');
                    $this->AuditPlugin()->auditProject(200, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $project->getProjectId());
                    
                    $this->synchronizePricing($products, $project->getProjectId());
                }
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    /**
     * synchronize installation ppu
     * @param \Space\Entity\Space $space
     * @return boolean
     * @throws \Exception
     */
    private function synchroniseInstallation (\Space\Entity\Space $space) {
        try {
            $query = $this->getEntityManager()->createQuery("SELECT SUM(s.ippu * s.quantity) AS price FROM Space\Entity\System s WHERE s.space = {$space->getSpaceId()}");
            $sum = $query->getSingleScalarResult();
            $sum = round($sum, 2);
            $systems=$this->getEntityManager()->getRepository('Space\Entity\System')->findBySpaceId($space->getSpaceId(), array('locked'=>true,'type'=>100));
            if (!empty($systems)) {
                $systemInstall = array_shift($systems);
            }

            if (empty($sum)) {
                if (!empty($systemInstall)) {
                    $this->getEntityManager()->remove($systemInstall);
                    $this->getEntityManager()->flush();
                } 
            } else {
                if (empty($systemInstall)) {
                    $products=$this->getEntityManager()->getRepository('Product\Entity\Product')->findByType(100);
                    if (empty($products)) {
                        throw new \Exception('Could not find installation product');
                    }
                    $product = array_shift($products);
                    $systemInstall = new \Space\Entity\System();
                    $systemInstall
                            ->setSpace($space)
                            ->setQuantity(1)
                            ->setIppu(0)
                            ->setHours(0)
                            ->setLux(0)
                            ->setOccupancy(0)
                            ->setLocked(true)
                            ->setLegacy(null)
                            ->setProduct($product);
                } else {
                    if ($systemInstall->getPpu()==$sum) {
                        return true;
                    }
                }

                $systemInstall
                        ->setCpu($sum)
                        ->setPpu($sum);
                $this->getEntityManager()->persist($systemInstall);
                $this->getEntityManager()->flush();
            }
            
            return true;

        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function trialAction()
    {
        $this->setCaption('Create Trial');
        
        $breakdown = $this->getModelService()->trialBreakdown($this->getProject());
        //$this->debug()->dump($breakdown);
        $form = new \Project\Form\ExportTrialForm();
        $form
            ->setAttribute('action', '/client-'.$this->getProject()->getClient()->getClientId().'/project-'.$this->getProject()->getProjectId().'/export/createtrial/')
            ->setAttribute('class', 'form-horizontal');
        $this->getView()
                ->setVariable('form', $form)
                ->setVariable('breakdown', $breakdown);/**/
        
		return $this->getView();
    }
    
    public function createTrialAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('illegal request');
            }
            
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('illegal method');
            }
                
            $form = new \Project\Form\ExportTrialForm();
            $form->setInputFilter(new \Project\Filter\ExportTrialFilter());
            $post = $this->params()->fromPost();
            $form->setData($post);
            
            
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                
                // check for duplication
                $query = $em->createQuery('SELECT count(p) FROM Project\Entity\Project p WHERE p.client='.$this->getProject()->getClient()->getClientId().' AND p.type=3 AND p.name=?1')->setParameter(1, $form->get('name')->getValue());
                $count = $query->getSingleScalarResult();
                if ($count) {
                    $data = array('err'=>true, 'info'=>array('name'=>array('alreadyexists'=>'Project name already exists')));
                } else {
                    $project = new \Project\Entity\Project();
                    $project
                            ->setClient($this->getProject()->getClient())
                            ->setSector($this->getProject()->getSector())
                            ->setFinanceProvider(null);
                    $info = array (
                        'status' => 1, 
                        'type' => 3, // trial
                        'name' => $form->get('name')->getValue(),
                        'co2' => $project->getCo2(),
                        'fueltariff' => $project->getFuelTariff(),
                        'rpi' => $project->getRpi(),
                        'epi' => $project->getEpi(),
                        'mcd' => $project->getMcd(),
                        'eca' => $project->getEca(),
                        'carbon' => $project->getCarbon(),
                        'test' => false,
                        'ibp' => false,
                        'weighting' => 0,
                        'financeYears' => 0,
                        'retrofit' => $project->getRetrofit(),
                    );
                    
                    $hydrator = new DoctrineHydrator($em,'Space\Entity\System');
                    $hydrator->hydrate($info, $project);
                    
                    $em->persist($project);
                    
                    if (!empty($post['systemId'])) {
                        // all trials use root space 
                        $space = new \Space\Entity\Space();
                        $space->setRoot(true);
                        $space->setName('root');
                        $space->setProject($project);
                        $em->persist($space);
                        
                        $systems = array();
                        foreach ($post['systemId'] as $i=>$systemId) {
                            $systems[$systemId] = array (
                                $post['quantity'][$i],
                                $post['tppu'][$i],
                            );
                        }
                        
                        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
                        $queryBuilder
                            ->select('s.systemId, sp.spaceId, p.productId AS product, l.legacyId AS legacy, s.cpu, s.ppu, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.label, s.locked, s.attributes')
                            ->from('Space\Entity\System', 's')
                            ->join('s.space', 'sp')
                            ->join('s.product', 'p')
                            ->leftjoin('s.legacy', 'l')
                            ->where('sp.project=?1')
                            ->andWhere('s.systemId IN ('.implode(',',  array_keys($systems)).')')
                            ->setParameter(1, $this->getProject()->getProjectId())
                            ;
        
                        $query = $queryBuilder->getQuery();
                        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
                        //$this->debug()->dump($result);
                        
                        foreach ($result as $systemData) {
                            $system = new \Space\Entity\System();
                            $system->setSpace($space);
                            $systemData['quantity'] = $systems[$systemData['systemId']][0];
                            $systemData['legacyQuantity'] = $systems[$systemData['systemId']][0];
                            $systemData['ppuTrial'] = $systems[$systemData['systemId']][1];
                            $systemData['ippu'] = 0;

                            //$this->debug()->dump($systemData);
                            unset($systemData['spaceId']);
                            unset($systemData['systemId']);
                            $hydrator = new DoctrineHydrator($em,'Space\Entity\System');
                            $hydrator->hydrate($systemData, $system);
                            $em->persist($system);
                        }/**/
                        
                        $installation = $form->get('installation')->getValue();
                        if (!empty($installation)) {
                            $products=$this->getEntityManager()->getRepository('Product\Entity\Product')->findByType(100); // installation product type
                            $product = array_shift($products);
                            $system = new \Space\Entity\System();
                            $system
                                ->setSpace($space)
                                ->setQuantity(1)
                                ->setIppu(0)
                                ->setHours(0)
                                ->setLux(0)
                                ->setOccupancy(0)
                                ->setLocked(false)
                                ->setLegacy(null)
                                ->setProduct($product)
                                ->setCpu($installation)
                                ->setPpu($installation);
                            $em->persist($system);
                        }
                        
                        $delivery = $form->get('delivery')->getValue();
                        if (!empty($delivery)) {
                            $products=$this->getEntityManager()->getRepository('Product\Entity\Product')->findByType(101); // installation product type
                            $product = array_shift($products);
                            $system = new \Space\Entity\System();
                            $system
                                ->setSpace($space)
                                ->setQuantity(1)
                                ->setIppu(0)
                                ->setHours(0)
                                ->setLux(0)
                                ->setOccupancy(0)
                                ->setLocked(false)
                                ->setLegacy(null)
                                ->setProduct($product)
                                ->setCpu($delivery)
                                ->setPpu($delivery);
                            $em->persist($system);
                        }
                        
                    }
                    
                    $em->flush();
                    
                    
                    $data = array('err'=>false, 'url'=>'/client-'.$project->getClient()->getClientId().'/trial-'.$project->getProjectId().'/');
                    $this->AuditPlugin()->auditProject(206, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $project->getProjectId());
                }
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
       
}