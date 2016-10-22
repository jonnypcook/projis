<?php
namespace Project\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class EmailForm extends Form 
{
    public function __construct()
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        

        $this->setAttribute('method', 'post');

        
        $this->add(array(     
            'name' => 'to',
            'type' => 'Select',       
            'attributes' =>  array(
                'class' => 'chzn-select span12',
                'multiple' => true,
                'data-placeholder' => "Click box to select contacts to send email"
            ),
            'options' => array(
            ),
        ));  
        
        $this->add(array(     
            'name' => 'cc',
            'type' => 'Select',       
            'attributes' =>  array(
                'class' => 'chzn-select span12',
                'multiple' => true,
                'data-placeholder' => "Click box to select contacts to cc email"
            ),
            'options' => array(
            ),
        ));  
        
        $this->add(array(
            'name' => 'subject', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'span12  popovers',
            ),
            'options' => array(
            ),
        ));

        $this->add(array(
            'name' => 'message', // 'usr_name',
            'attributes' => array(
                'type'  => 'textarea',
                'class' => 'span12  wysihtmleditor5',
                'rows' => 10,
            ),
            'options' => array(
            ),
        ));

    }
    
   
}