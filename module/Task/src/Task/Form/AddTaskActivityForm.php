<?php
namespace Task\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class AddTaskActivityForm extends Form 
{
    public function __construct()
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'note', // 'label',
            'attributes' => array(
                'type'  => 'textarea',
                'data-original-title' => 'Update on the task progress',
                'data-trigger' => 'hover',
                'data-placement' => 'bottom',
                'class' => 'span12  tooltips',
                'rows' => 6,
            ),
            'options' => array(
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