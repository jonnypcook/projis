<?php
namespace Client\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ClientCreateForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Client\Entity\Client'));

        
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'This is the unique name of the client',
                'data-original-title' => 'Client Name',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'regno', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'This is the registration number of the company',
                'data-original-title' => 'Client Registration Number',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(     
            'name' => 'paymentTerms',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'The payment terms of the client',
                'data-original-title' => 'Payment Terms',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Payment Term"
            ),
            'options'=> array (
                'object_manager' => $this->getObjectManager(),
                'target_class' => 'Client\Entity\PaymentTerms',
                'property' => 'description',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        //'orderBy' => array('name' => 'ASC')
                    )
                ) 
            )
        )); 
        
        $this->add(array(
            'name' => 'url', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'Website URL for the company',
                'data-original-title' => 'Website Address',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'user',
            'attributes' =>  array(
                'data-content' => 'The relationship owner of the client',
                'data-original-title' => 'Owner',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose an owner"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Application\Entity\User',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                },/**/
            ),
        ));    

        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'source',
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Client\Entity\Source',
                'property'       => 'name',
            ),
            'attributes' =>  array(
                'data-content' => 'The source of the client',
                'data-original-title' => 'Client Source',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Source"
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