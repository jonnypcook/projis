<?php
namespace Contact\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ContactForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, $clientId)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Contact\Entity\Contact'));

        
        $this->setAttribute('method', 'post');

        
        $this->add(array(     
            'name' => 'titleId',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Title of the contact',
                'data-original-title' => 'Title',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Title"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\Title',
                'property' => 'name',/**/
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
            'name' => 'buyingtypeId',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Buying type of the contact',
                'data-original-title' => 'Buying Type',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Buying Type"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\BuyingType',
                'property' => 'name',/**/
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
            'name' => 'influenceId',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Influence of the contact',
                'data-original-title' => 'Influence',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose an Influence"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\Influence',
                'property' => 'name',/**/
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
            'name' => 'modeId',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Current mode of the contact',
                'data-original-title' => 'Mode',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Mode"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\Mode',
                'property' => 'name',/**/
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
            'name' => 'addressId',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Address of the property',
                'data-original-title' => 'Address',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
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
        
        
        $this->add(array(
            'name' => 'forename', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'the first name of the contact',
                'data-original-title' => 'Forename',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'keywinresult', // 'usr_name',
            'type'  => 'textarea',
            'attributes' => array(
                'data-content' => 'A short statement of the personal Win that a Buying Influence attains when important measurable business Results are delivered.',
                'data-original-title' => 'Key Win Result',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'surname', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'The surname of the contact',
                'data-original-title' => 'Surname',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'telephone1', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'The primary phone number of the contact',
                'data-original-title' => 'Primary Phone Number',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'telephone2', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Additional phone number of the contact',
                'data-original-title' => 'Additional Phone Number',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'email', // 'usr_name',
            'type'  => 'email',
            'attributes' => array(
                'data-content' => 'The email address of the contact',
                'data-original-title' => 'Email Address',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'position', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'The business position of the contact',
                'data-original-title' => 'Position',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
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