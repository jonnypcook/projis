<?php
namespace Product\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ProductPhilipsForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, array $config = array())
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Product\Entity\Product'));

        
        $this->setAttribute('method', 'post');
        
        $itemMode = !empty($config['itemMode']);

        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'type',
            'attributes' =>  array(
                'data-original-title' => 'The product type',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'class' => 'span'.($itemMode?'6':'12').' chzn-select tooltips',
            ),
            'options' => array(
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Product\Entity\Type',
                'property'       => 'description',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array('service'=>0),
                        'orderBy' => array('description' => 'ASC')
                    )
                )                 
                
             ),
        ));  
        
        
        $this->add(array(
            'name' => 'eca', // 'usr_password',
            'type'  => 'CheckBox',
            'attributes' => array(
            ),
            'options' => array(
            ),
        ));
        
        
        
        $this->add(array(
            'name' => 'pwr', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Power (watts) consumption of the product',
                'data-trigger' => 'hover',
                'data-placement' => 'right',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(
            'name' => 'description', // 'usr_name',
            'attributes' => array(
                'type'  => 'textarea',
                'data-original-title' => 'Description of product to appear on itemization',
                'data-trigger' => 'hover',
                'data-placement' => 'right',
                'class' => 'span12  tooltips',
                'rows' => 4,
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