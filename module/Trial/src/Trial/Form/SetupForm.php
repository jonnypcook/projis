<?php
namespace Trial\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class SetupForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, \Client\Entity\Client $client)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Project\Entity\Project'));

        
        $this->setAttribute('method', 'post');
        
        $this->add(array(     
            'name' => 'contacts',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'class' => 'chzn-select span6',
                'data-trigger' => 'hover',
                'multiple' => true,
                'style' => 'width: 400px!important'
            ),
            'options' => array(
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Contact\Entity\Contact',
                'order_by'=>'forename',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->getTitle()->getDisplay().' '.$targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                },/**/
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByClientId',
                    'params' => array(
                        'client_id'=>$client->getClientId(),
                    )
                ) 
            ),
        ));  

        $this->add(array(
            'name' => 'name', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'This is the unique name by which this project will be referenced',
                'data-original-title' => 'Project Name',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'test', // 'usr_password',
            'type'  => 'CheckBox',
            'attributes' => array(
            ),
            'options' => array(
            ),
        ));

        
        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'type',
            'attributes' =>  array(
                'data-content' => 'The type of project and how it will be delivered',
                'data-original-title' => 'Project Type',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Type"
            ),
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Project\Entity\Type',
                'property'       => 'name',
            ),
        ));    

        $this->add(array(     
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'name' => 'sector',
            'options' => array(
                'empty_option' => 'Please Select',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Project\Entity\Sector',
                'property'       => 'name',
            ),
            'attributes' =>  array(
                'data-content' => 'The business sector in which this project sits',
                'data-original-title' => 'Project Sector',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Sector"
            ),
        ));    
        
        /*$this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'status',
            'attributes' =>  array(
                'data-content' => 'The progress status of the project',
                'data-original-title' => 'Project Status',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Status"
            ),
            'options' => array(
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Project\Entity\Status',
                'empty_option' => 'Please Select',
                'property'       => 'name',
                'is_method'      => true,
                /*'find_method'    => array(
                    'name'   => 'findBy',
                    'params' => array(
                        'criteria' => array('weighting' => '1'),
                    ),
                ),/**//*
            ),
            
        ));/**/
        
        $this->add(array(     
            'type' => 'Select',       
            'name' => 'retrofit',
            'attributes' =>  array(
                'data-content' => 'Is this a retrofit or a new-build project',
                'data-original-title' => 'Build Type',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a build type"
            ),
            'options' => array (
                'value_options' => array (1 => 'Retrofit', 0=>'New Build',)
            )

        ));

        $this->add(array(     
            'type' => 'Select',       
            'name' => 'financeProvider',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'attributes' =>  array(
                'data-content' => 'The provider of finance for the project if funded',
                'data-original-title' => 'Finance Provider',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Finance Provider"
            ),
            'options' => array(
                'empty_option' => 'No Finance',
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Project\Entity\FinanceProvider',
                'property'       => 'name',
            ),
        ));
        
        $this->add(array(     
            'type' => 'Select',       
            'name' => 'financeYears',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'attributes' =>  array(
                'data-content' => 'The finance option for the funcded project',
                'data-original-title' => 'Finance Years',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a Finance Option",
                
                //
            ),
            'options' => array (
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Project\Entity\FinanceYears',
                'property'       => 'name',
            )
        ));
        
        $this->add(array(
            'name' => 'co2', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Annual CO2 footprint',
                'data-original-title' => 'Carbon Footprint',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'fuelTariff', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Current fuel tariff per unit for business',
                'data-original-title' => 'Fuel Tariff',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'rpi', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Current (anticipated) retail price index for next period',
                'data-original-title' => 'Retail Price Index',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'epi', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Current (anticipated) energy price index for next period',
                'data-original-title' => 'Energy Price Index',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'mcd', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Discount on project items',
                'data-original-title' => 'MCD',
                'data-trigger' => 'hover',
                'class' => 'span5  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'maintenance', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'Legacy maintenance costs',
                'data-original-title' => 'maintenance',
                'data-trigger' => 'hover',
                'class' => 'span8  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(     
            'type' => 'Select',       
            'name' => 'eca',
            'attributes' =>  array(
                'data-content' => 'Enhanced capital allowance percentage',
                'data-original-title' => 'ECA',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose an ECA factor"
            ),
            'options' => array (
                'value_options' => array (0 => 'None', '0.20' => '20%', '0.21' => '21%', '0.23' => '23%', '0.24' => '24%',)
            )

        ));
        
        $this->add(array(     
            'type' => 'Select',       
            'name' => 'carbon',
            'attributes' =>  array(
                'data-content' => 'Carbon allowance factor',
                'data-original-title' => 'Carbon Allowance',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a carbon factor"
            ),
            'options' => array (
                'value_options' => array (0 => 'None','12' => '12.00','16' => '16.00',)
            )
        ));
        
        $this->add(array(     
            'type' => 'Select',       
            'name' => 'ibp',
            'attributes' =>  array(
                'data-content' => 'The warranty type by which the LED products are covered',
                'data-original-title' => 'Warranty Type',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a warranty type"
            ),
            'options' => array (
                'value_options' => array (0 => 'Standard Warranty',1 => 'Insurance Backed Premium',),
            )
        ));
        
        $this->add(array(     
            'type' => 'Select',       
            'name' => 'model',
            'attributes' =>  array(
                'data-content' => 'Default period in years over which to run the forecast',
                'data-original-title' => 'Default Model',
                'data-trigger' => 'hover',
                'class' => 'span6  popovers',
                'data-placeholder' => "Choose a default model"
            ),
            'options' => array (
                'value_options' => array (3 => '3 Years',4 => '4 Years',5 => '5 Years',6 => '6 Years',7 => '7 Years',8 => '8 Years',9 => '9 Years',10 => '10 Years',)
            )
        ));
        
        $this->add(array(
            'name' => 'weighting', // 'usr_name',
            'type'  => 'hidden',
            'attributes' => array(
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(     
            'name' => 'states',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'class' => 'chzn-select',
                'multiple' => true,
                'style' => 'width: 300px',
                'data-placeholder' => "Click box to select state tags"
            ),
            'options' => array(
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Application\Entity\State',
                'order_by'=>'name',
                'property'       => 'name',
            ),
        ));
        
        
        $this->add(array(
            'name' => 'installed', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Date of commencement of trial',
                'data-placement' => 'left',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                //'readonly' => 'true',
            ),
            'options' => array(
                'format' => 'd/m/Y'
            ),
        ));
        

        $this->add(array(
            'name' => 'completed', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Date of completion of trial',
                'data-placement' => 'left',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                //'readonly' => 'true',
            ),
            'options' => array(
                'format' => 'd/m/Y'
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