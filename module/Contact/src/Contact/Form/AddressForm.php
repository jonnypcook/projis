<?php
namespace Contact\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class AddressForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Contact\Entity\Address'));

        
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'postcode', // 'usr_name',
            'type'  => 'text',
            'required' => true,
            'attributes' => array(
                'data-content' => 'The postcode of the address',
                'data-original-title' => 'Postcode',
                'data-trigger' => 'hover',
                'class' => 'span4  popovers',
                'data-placement'=>'bottom',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'line1', // 'usr_name',
            'type'  => 'text',
            'required' => true,
            'attributes' => array(
                'data-content' => 'The first line of the address',
                'data-original-title' => 'Address Line 1',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placement'=>'bottom',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'line2', // 'usr_name',
            'type'  => 'text',
            'required' => false,
            'attributes' => array(
                'data-content' => 'The second line of the address',
                'data-original-title' => 'Address Line 2',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placement'=>'top',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'line3', // 'usr_name',
            'type'  => 'text',
            'required' => false,
            'attributes' => array(
                'data-content' => 'The third line of the address',
                'data-original-title' => 'Address Line 3',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placement'=>'top',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'line4', // 'usr_name',
            'type'  => 'text',
            'required' => false,
            'attributes' => array(
                'data-content' => 'The town or city of the address',
                'data-original-title' => 'Town/City',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placement'=>'top',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'line5', // 'usr_name',
            'type'  => 'text',
            'required' => false,
            'attributes' => array(
                'data-content' => 'The region or county of the address',
                'data-original-title' => 'Region/County',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placement'=>'top',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(     
            'name' => 'country',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'data-content' => 'Country of origin of the address',
                'data-original-title' => 'Country',
                'data-trigger' => 'hover',
                'data-placement'=>'top',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Country",
                'value'=>183,
            ),
            'options' => array(
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\Country',
                'property' => 'name',/**/
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array('enabled'=>true),
                        'orderBy' => array('name' => 'ASC')
                    )
                ) 
            ),
        )); 
        
        $this->add(array(
            'name' => 'lat', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Latitude of the address',
                'data-original-title' => 'Latitude',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
                'data-placement'=>'top',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'lng', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Longitude of the address',
                'data-original-title' => 'Longitude',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
                'data-placement'=>'top',
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