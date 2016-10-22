<?php
namespace Task\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class AddTaskForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Task\Entity\Task'));

        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'description', // 'label',
            'attributes' => array(
                'type'  => 'textarea',
                'data-original-title' => 'Further information about the task',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'class' => 'span12  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'taskType',
            'attributes' =>  array(
                'data-original-title' => 'Type of the task to be created',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'class' => 'span12 tooltips',
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Task\Entity\TaskType',
                'property'       => 'name',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy' => array('name' => 'ASC')
                    )
                )                 
                
             ),
        ));
        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'taskStatus',
            'attributes' =>  array(
                'data-original-title' => 'Status of the task to be created',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'class' => 'span12 tooltips',
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Task\Entity\TaskStatus',
                'property'       => 'name',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy' => array('name' => 'ASC')
                    )
                )                 
                
             ),
        ));
        
        $this->add(array(     
            'name' => 'users',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'class' => 'chzn-select',
                'multiple' => true,
                'data-placeholder' => "Select users to invite to task"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Application\Entity\User',
                'order_by'=>'forename',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                },/**/
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy' => array('forename' => 'ASC')
                    )
                ) 
            ),
        ));
                
        $this->add(array(
            'name' => 'required', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Desired completion date of the task',
                'data-placement' => 'left',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                //'readonly' => 'true',
                'value' => date('d/m/Y'),
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