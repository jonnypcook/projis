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


class CompetitorController extends AuthController
{
    
    public function addAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('Illegal request type');
            }
            
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('illegal method');
            }

            $post = $this->getRequest()->getPost();
            $competitor = new \Application\Entity\Competitor();
            $form = new \Application\Form\CompetitorAddForm($this->getEntityManager());
            $form->bind($competitor);
            $form->setData($post);
            if ($form->isValid()) {
                $form->bindValues();
                if (!empty($post['competitorCoreStrength'])) {
                    $arr = array();
                    foreach ($post['competitorCoreStrength'] as $value) {
                        if (!empty(trim($value))) {
                            $arr[] = $value;
                        }
                    }
                    if (!empty($arr)) {
                        $competitor->setStrengths(json_encode($arr));
                    }
                }
                
                if (!empty($post['competitorCoreWeakness'])) {
                    $arr = array();
                    foreach ($post['competitorCoreWeakness'] as $value) {
                        if (!empty(trim($value))) {
                            $arr[] = $value;
                        }
                    }
                    if (!empty($arr)) {
                        $competitor->setWeaknesses(json_encode($arr));
                    }
                }
                
                $this->getEntityManager()->persist($competitor);
                $this->getEntityManager()->flush();
                
                $data = array('err'=>false, 'info'=>array(
                    'name'=>$competitor->getName(),
                    'cid'=>$competitor->getCompetitorId()
                ));
                //$this->AuditPlugin()->auditProject(202, $this->getUser()->getUserId(), $this->getProject()->getClient()->getClientId(), $this->getProject()->getProjectId());
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    public function listAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new \Exception('Illegal request type');
            }
            
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('illegal method');
            }

            $post = $this->getRequest()->getPost();
            
            
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb
                ->select('c.name, c.competitorId')
                ->from('Application\Entity\Competitor', 'c');
        
            $query  = $qb->getQuery();
            $competitorsTmp = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $competitors = array();
            foreach ($competitorsTmp as $data) {
                $competitors[$data['competitorId']] = $data;
            }
            
            if (!empty($post['pid'])) {
                $qb = $this->getEntityManager()->createQueryBuilder();
                $qb
                    ->select('c.competitorId')
                    ->from('Project\Entity\ProjectCompetitor', 'pc')
                    ->innerJoin('pc.competitor', 'c')
                    ->where('pc.project = '.$post['pid'])
                        ;
        
                $query  = $qb->getQuery();
                $exclude = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
                
                foreach ($exclude as $competitor) {
                    if (isset($competitors[$competitor['competitorId']])) {
                        unset ($competitors[$competitor['competitorId']]);
                    }
                }
                
            }
            
            
            $data = array('err'=>false, 'info'=>$competitors);
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }

        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
}
