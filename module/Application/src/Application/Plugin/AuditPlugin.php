<?php
namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class AuditPlugin extends AbstractPlugin
{
    /**
     * add audit entry for user
     * @param int $type
     * @param int $user
     * @param array $data
     * @return \Application\Entity\Audit
     */
    public function audit($type, $user, array $data=array()) {
        try {
            if (!empty($data['data'])) {
                if (is_array($data['data'])) {
                    $data['data'] = json_encode($data['data']);
                }
            }
                        
            $em = $this->getController()->getServiceLocator()->get('doctrine.entitymanager.orm_default');
            $audit = new \Application\Entity\Audit();
            $hydrator = new DoctrineHydrator($em,'Application\Entity\Audit');
            $hydrator->hydrate(array_merge(
                array(
                    'user'=>$user,
                    'auditType'=>$type, 
                    'client'=>null, 
                    'project'=>null,
                    'space'=>null,
                    'product'=>null,
                    'documentCategory'=>null,
                ),
                $data
            ), $audit);
            
            
            $em->persist($audit);
            $em->flush();
            
            return $audit;
            
        } catch (\Exception $ex) {}
    }
    
    
    /**
     * add audit entry for client
     * @param int $type
     * @param int $user
     * @param int $client
     * @param array $data
     */
    public function auditClient($type, $user, $client, array $data=array()) {
        $data['client'] = $client;
        $this->audit($type, $user, $data);
    }

    
    /**
     * add project audit entry
     * @param int $type
     * @param int $user
     * @param int $client
     * @param int $project
     * @param array $data
    */
    public function auditProject($type, $user, $client, $project, array $data=array()) {
        $data['client'] = $client;
        $data['project'] = $project;
        $this->audit($type, $user, $data);
    }
    
    
    /**
     * add space audit
     * @param int $type
     * @param int $user
     * @param int $client
     * @param int $project
     * @param int $space
     * @param array $data
     */
    public function auditSpace($type, $user, $client, $project, $space, array $data=array()) {
        $data['client'] = $client;
        $data['project'] = $project;
        $data['space'] = $space;
        $this->audit($type, $user, $data);
    }
    
    
    /**
     * add activity
     * @param int $type
     * @param int $user
     * @param string $note
     * @param array $data
     * @return \Application\Entity\Activity
     */
    public function activity($type, $user, $note, array $data=array()) {
        try {
            if (!empty($data['data'])) {
                if (is_array($data['data'])) {
                    $data['data'] = json_encode($data['data']);
                }
            }
            
            $startDt = ($data['start'] instanceof \DateTime)?$data['start']:new \DateTime();
            $duration = preg_match('/^[\d]+$/', $data['duration'])?$data['duration']:5;
            
            unset($data['start']);
            unset($data['duration']);
            
            if (!empty($data['data'])) {
                if (is_array($data['data'])) {
                    $data['data'] = json_encode($data['data']);
                }
            }
            
            $endDt = new \DateTime();
            $endDt->setTimestamp($startDt->getTimestamp()+($duration*60));
                        
            $em = $this->getController()->getServiceLocator()->get('doctrine.entitymanager.orm_default');
            $activity = new \Application\Entity\Activity();
            $hydrator = new DoctrineHydrator($em,'Application\Entity\Activity');
            $hydrator->hydrate(array_merge(
                array(
                    'user'=>$user,
                    'activityType'=>$type, 
                    'client'=>null, 
                    'project'=>null,
                    'note'=>$note,
                ),
                $data
            ), $activity);
            
            $activity
                    ->setStartDt($startDt)
                    ->setEndDt($endDt);
            
            $em->persist($activity);
            $em->flush();
            
            return $activity;
            
        } catch (\Exception $ex) {}
    }
    
    /**
     * add client based activity
     * @param int $type
     * @param int $user
     * @param int $client
     * @param string $note
     * @param array $data
     */
    public function activityClient($type, $user, $client, $note, array $data=array()) {
        $data['client'] = $client;
        $this->activity($type, $user, $note, $data);
    }

    /**
     * add project based activity
     * @param int $type
     * @param int $user
     * @param int $client
     * @param int $project
     * @param string $note
     * @param array $data
     */
    public function activityProject($type, $user, $client, $project, $note, array $data=array()) {
        $data['client'] = $client;
        $data['project'] = $project;
        
        $this->activity($type, $user, $note, $data);
    }

    
}