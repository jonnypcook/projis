<?php
namespace Task\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ChangeDeliveryForm extends Form
{
    public function __construct()
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);

        $this->setAttribute('method', 'post');
        
        
        $this->add(array(
            'name' => 'required', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Desired completion date of the task',
                'data-placement' => 'left',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                //'readonly' => 'true',
                'value' => date('d/m/Y'),
            ),
            'options' => array(
            ),
        ));

        
    }
   
}