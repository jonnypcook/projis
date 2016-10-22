<?php
namespace Project\Form;

use Zend\Form\Form;


class ExportTrialForm extends Form 
{
    public function __construct()
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'name', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'This is the unique name by which this trial will be referenced',
                'data-original-title' => 'Trial Name',
                'data-trigger' => 'hover',
                'data-placement' => 'bottom',
                'class' => 'span12  popovers',
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(
            'name' => 'installation', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'This is the cost of installation (if applicable)',
                'data-original-title' => 'Installation Cost',
                'data-trigger' => 'hover',
                'data-placement' => 'bottom',
                'class' => 'span12  popovers',
                'value'=>'0.00',
            ),
            'options' => array(
            ),
        ));
        
        
        $this->add(array(
            'name' => 'delivery', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'This is the cost of delivery (if applicable)',
                'data-original-title' => 'Delivery Cost',
                'data-trigger' => 'hover',
                'data-placement' => 'top',
                'class' => 'span12  popovers',
                'value'=>'0.00',
            ),
            'options' => array(
            ),
        ));
        
        
        
    }
    
}