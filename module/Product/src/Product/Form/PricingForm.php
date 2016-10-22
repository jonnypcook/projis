<?php
namespace Product\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class PricingForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Product\Entity\Pricing'));

        
        $this->setAttribute('method', 'post');
        

        
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
            'name' => 'min', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'data-original-title' => 'Minimum quantity that qualifies for this range',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'max', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'data-original-title' => 'Maximum quantity that qualifies for this range',
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