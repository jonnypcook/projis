<?php
namespace Job\Form;

use Zend\Form\Form;


class DeliveryNoteForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, \Project\Entity\Project $project)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'sent', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Delivery date of products',
                'data-placement' => 'left',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips span6',
                //'readonly' => 'true',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'reference', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Additional textual reference for delivery note if required',
                'data-placement' => 'bottom',
                'data-trigger' => 'hover',
                'class' => 'tooltips span10',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'deliveredby', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'who is the delivery being made by',
                'data-placement' => 'bottom',
                'data-trigger' => 'hover',
                'class' => 'tooltips span10',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(     
            'type' => 'Select',       
            'name' => 'address',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'attributes' =>  array(
                'data-original-title' => 'Delivery address for product items',
                'data-trigger' => 'hover',
                'class' => 'span10  tooltips',
                'data-placement' => 'left',
                'data-placeholder' => "Choose an Address",

                //
            ),
            'options' => array(
                'empty_option' => 'Items to be picked up',
                'label' => 'Delivery Address',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\Address',
                'order_by'=>'line1',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->assemble();
                },/**/
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByClientId',
                    'params' => array(
                        'client_id' => $project->getClient()->getClientId(),
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