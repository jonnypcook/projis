<?php
namespace Client\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class BuildingCreateForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, $clientId)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Client\Entity\Building'));

        
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name', // 'usr_name',
            'type'  => 'text',
            
            'required' => true,
            'attributes' => array(
                'data-content' => 'This is the name by which this building will be referenced',
                'data-original-title' => 'Building Name',
                'data-trigger' => 'hover',
                'data-placement'=>'top',
                'class' => 'span12  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'notes',
            'type'  => 'textarea',
            'required' => false,
            'attributes' => array(
                'data-content' => 'Additional notes on the building',
                'data-original-title' => 'Notes',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'style' => 'height: 100px',
            ),
            'options' => array(
            ),
        ));
        
        
        
        $this->add(array(     
            'name' => 'addressId',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Address of the property',
                'data-original-title' => 'Address',
                'data-trigger' => 'hover',
                'class' => 'span6 popovers',
                'data-placeholder' => "Select an address"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\Address',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->getPostcode() . ' ' . $targetEntity->getLine1();
                },/**/
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByClientId',
                    'params' => array(
                        'client_id' => $clientId,
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