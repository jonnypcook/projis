<?php
namespace Application\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class RemotePhosphorForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        
        $this->add(array(
            'name' => 'length', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Maximum required length of the fitting',
                'data-placement'=>'right',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        
        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'productId',
            'attributes' =>  array(
                'data-original-title' => 'The architectural product',
                'data-trigger' => 'hover',
                'class' => 'span12 chzn-select tooltips',
                'data-placement'=>'left',
                //'data-placeholder' => "Choose a Building"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Product\Entity\Product',
                'property'       => 'model',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByType',
                    'params' => array(
                        'typeId' => 3,
                        'orderBy' => array('model' => 'ASC')
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