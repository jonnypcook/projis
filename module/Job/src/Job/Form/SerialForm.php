<?php
namespace Job\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class SerialForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, \Project\Entity\Project $project)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        
        $this->setAttribute('method', 'post');
        
        $this->add(array(     
            'name' => 'projectId',
            'type' => 'hidden',       
            'attributes' =>  array(
                'value' => $project->getProjectId(),
            ),
        ));  
        
        
        $this->add(array(     
            'name' => 'products',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'class' => 'span10',
            ),
            'options' => array(
                'empty_option' => 'Select product to find placement',
                'property'       => 'model',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Product\Entity\Product',
                'order_by'=>'model',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByProjectId',
                    'params' => array(
                        'projectId'=>$project->getProjectId(),
                    )
                ) 
            ),
        ));  
        
        $this->add(array(
            'name' => 'range', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'data-content' => 'The serial range to input',
                'data-original-title' => 'Range',
                'data-trigger' => 'hover',
                'class' => 'span3  popovers',
                'value'=>1,
                'min'=>1,
                'max'=>50
            ),
            'options' => array(
                
            ),
        ));

        $this->add(array(
            'name' => 'serialStart', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'data-content' => 'The first serial number in the range',
                'data-original-title' => 'Serial Number (start)',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'min'=>1
            ),
            'options' => array(
            ),
        ));
        
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