<?php
namespace Product\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class LegacyConfigForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
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
            'name' => 'category',
            'attributes' =>  array(
                'data-original-title' => 'Product Category',
                'data-trigger' => 'hover',
                'class' => 'span'.($itemMode?'6':'12').' tooltips',
                //'data-placeholder' => "Choose a Building"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Product\Entity\Category',
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
            'name' => 'description', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Description of the legacy item',
                'data-trigger' => 'hover',
                'class' => 'span12  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'quantity', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'quantity per item',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
                'value' => 1,
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'pwr_item', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Power (watts) consumption per item',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
                'value' => 0,
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(
            'name' => 'pwr_ballast', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Power ballast of the item',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
                'value' => 0,
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'emergency', // 'usr_password',
            'type'  => 'CheckBox',
            'attributes' => array(
            ),
            'options' => array(
            ),
        ));
        
        
        
        $this->add(array(
            'name' => 'dim_item', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Item dimensions (e.g. 4ft)',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'dim_unit', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Total unit dimension (e.g. 600x600)',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'product',
            'attributes' =>  array(
                'data-original-title' => 'The 8point3 product',
                'data-trigger' => 'hover',
                'class' => 'span12 chzn-select tooltips',
                //'data-placeholder' => "Choose a Building"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Product\Entity\Product',
                'property'       => 'model',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy' => array('model' => 'ASC')
                    )
                )                 
                
             ),
        ));     /**/
        
        
        
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