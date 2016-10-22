<?php
namespace Application\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ActivityAddForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, array $config=array())
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Application\Entity\Activity'));

        
        $this->setAttribute('method', 'post');
        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'activityTypeId',
            'attributes' =>  array(
                'style'=> 'text-transform: capitalize', 
                'data-original-title' => 'Type of the activity',
                'class' => 'span12 tooltips',
                'value'=>500,
            ),
            'options' => array(
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Application\Entity\ActivityType',
                'property'       => 'name',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByCompatibility',
                    'params' => array(
                        'compatibility' => 1,
                    )
                )                 
                
             ),
        ));
        
        
        $this->add(array(
            'name' => 'duration', 
            'attributes' => array(
                'type'  => 'number',
                'data-original-title' => 'Duration of activity in minutes',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
                'value' => 5,
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'startDt', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Start date of the activity',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                //'readonly' => 'true',
                'value' => date('d/m/Y'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'startTm', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Start time of the activity',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
                'data-mask' => '99:99',
                //'readonly' => 'true',
                'value' => date('H:i'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'note', 
            'attributes' => array(
                'type'  => 'text',
                'class' => ' ',
                //'readonly' => 'true',
                'placeholder'=>'Enter details of the activity'
            ),
            'options' => array(
            ),
        ));
        
        if (!empty($config['projectId'])) {
            $this->add(array(
                'name' => 'projectId', 
                'attributes' => array(
                    'type'  => 'hidden',
                    'value' => $config['projectId'],
                ),
                'options' => array(
                ),
            ));
        } elseif (!empty($config['clientId'])) {
            $this->add(array(
                'name' => 'clientId', 
                'attributes' => array(
                    'type'  => 'hidden',
                    'value' => $config['clientId'],
                ),
                'options' => array(
                ),
            ));
            
            $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'projectId',
            'attributes' =>  array(
                'data-original-title' => 'Project Name',
                'data-trigger' => 'hover',
                'class' => 'span12 tooltips',
            ),
            'options' => array(
                'empty_option' => 'Project Name (if applicable)',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Project\Entity\Project',
                'property'       => 'name',
                'is_method' => true,
                
                'find_method' => array(
                    'name' => 'findByClientId',
                    'params' => array(
                        'clientId' => $config['clientId'],
                        'criteria' => array(),
                        'orderBy' => array('description' => 'ASC')
                    )
                )                 
                
             ),
        ));
        }
        
        
    }
    
    protected $objectManager;
    
    public function setObjectManager(\Doctrine\Common\Persistence\ObjectManager $objectManager)
    {
    	$this->objectManager = $objectManager;
    }
    
    public function getObjectManager()
    {
    	return $this->objectManager;
    }
}