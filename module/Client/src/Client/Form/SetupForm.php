<?php
namespace Client\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class SetupForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
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
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'This is the unique name by which this client will be referenced',
                'data-original-title' => 'Client Name',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'regno',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'This is the company registration number',
                'data-original-title' => 'Company Regstration Number',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Url',
            'attributes' => array(
                'data-content' => 'Company corporate website url',
                'data-original-title' => 'Company Website',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(     
            'name' => 'user',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'The 8point3 user who owns the client relationship',
                'data-original-title' => 'Owner',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a User"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Application\Entity\User',
                'order_by'=>'forename',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                },/**/
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy' => array('forename' => 'ASC')
                    )
                ) 
            ),
        ));    
        
        $this->add(array(     
            'name' => 'source',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'From what source did the client come from?',
                'data-original-title' => 'Source',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Source"
            ),
            'options'=> array (
                'empty_option' => '',
                'object_manager' => $this->getObjectManager(),
                'target_class' => 'Client\Entity\Source',
                'property' => 'name',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy' => array('name' => 'ASC')
                    )
                ) 
            )
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
            'name' => 'financeStatus',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Financial rating of the organisation',
                'data-original-title' => 'Finance Status',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Status"
            ),
            'options'=> array (
                'empty_option' => 'To Be Confirmed',
                'object_manager' => $this->getObjectManager(),
                'target_class' => 'Client\Entity\FinanceStatus',
                'property' => 'name',
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy' => array('financeStatusId' => 'ASC')
                    )
                ) 
            )
        ));   
        
        $this->add(array(
            'name' => 'fund', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Available fund for active client projects',
                'data-original-title' => 'Available Fund',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
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