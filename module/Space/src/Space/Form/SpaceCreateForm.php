<?php
namespace Space\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class SpaceCreateForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, $clientId)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Space\Entity\Space'));

        
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'This is the unique name by which this space will be referenced',
                'data-trigger' => 'hover',
                'class' => 'span12  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'building',
            'attributes' =>  array(
                'data-original-title' => 'The building to which the space belongs',
                'data-trigger' => 'hover',
                'class' => 'span12  tooltips',
                'data-placeholder' => "Choose a Building"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Client\Entity\Building',
                'property'       => 'name',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByClientId',
                    'params' => array(
                        'client_id' => $clientId,
                    )
                )             
             ),
        ));    

        $this->add(array(
            'name' => 'floor', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'min' => 0,
                'data-original-title' => 'The floor number of the building that the space is on',
                'data-trigger' => 'hover',
                'class' => 'span3  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'dimx', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'min' => 0,
                'data-original-title' => 'The width of the space',
                'data-trigger' => 'hover',
                'class' => 'span3  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(
            'name' => 'dimy', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'min' => 0,
                'data-original-title' => 'The depth of the space',
                'data-trigger' => 'hover',
                'class' => 'span3  tooltips',
            ),
            'options' => array(
            ),
        ));
        
         $this->add(array(
            'name' => 'dimh', // 'usr_name',
            'attributes' => array(
                'type'  => 'number',
                'min' => 0,
                'data-original-title' => 'The height of the space',
                'data-trigger' => 'hover',
                'class' => 'span3  tooltips',
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