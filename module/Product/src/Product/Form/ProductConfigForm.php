<?php
namespace Product\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ProductConfigForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
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
            'name' => 'brand',
            'attributes' =>  array(
                'data-original-title' => 'The product brand',
                'data-trigger' => 'hover',
                'class' => 'span'.($itemMode?'6':'12').' chzn-select tooltips',
                //'data-placeholder' => "Choose a Building"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Product\Entity\Brand',
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
            'name' => 'build',
            'attributes' =>  array(
                'data-original-title' => 'The product build type',
                'data-trigger' => 'hover',
                'class' => 'span'.($itemMode?'6':'12').' chzn-select tooltips',
                //'data-placeholder' => "Choose a Building"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Product\Entity\Build',
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
            'name' => 'type',
            'attributes' =>  array(
                'data-original-title' => 'The product type',
                'data-trigger' => 'hover',
                'class' => 'span'.($itemMode?'6':'12').' chzn-select tooltips',
            ),
            'options' => array(
                'empty_option' => 'Please Select',
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
            'name' => 'model', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Product code',
                'data-trigger' => 'hover',
                'class' => 'span'.($itemMode?'6':'12').'  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'description', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Description of the product',
                'data-trigger' => 'hover',
                'class' => 'span12  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'cpu', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Cost per unit of the product',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'ppu', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Price per unit of the product',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'ibppu', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Insurance-based premium price per unit of the product',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'ppuTrial', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Trial price per unit of the product',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'active', // 'usr_password',
            'type'  => 'CheckBox',
            'attributes' => array(
            ),
            'options' => array(
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
            'name' => 'mcd', // 'usr_password',
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
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        
        
        
        $this->add(array(
            'name' => 'sagepay', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'SagePay Product Code',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
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