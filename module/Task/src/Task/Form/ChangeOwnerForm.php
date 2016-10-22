<?php
namespace Task\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ChangeOwnerForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
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
            'name' => 'users',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'class' => 'chzn-select ',
                'multiple' => true,
                'data-placeholder' => "Select users to invite to task",
                'style'=>'width:300px',
                'rows'=>3
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