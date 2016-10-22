<?php
namespace Application\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class CalendarEventAddForm extends Form 
{
    public function __construct(array $config=array())
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'title', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Title of the event',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'location', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Location of the event',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'description', 
            'attributes' => array(
                'type'  => 'textarea',
                'data-original-title' => 'More detailed description of the event',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calStartDt', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Start date of the event',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                'style' => 'width: 100px',
                //'readonly' => 'true',
                'value' => date('d/m/Y'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calStartTm', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Start time of the event',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
                'style' => 'width: 100px',
                'data-mask' => '99:99',
                //'readonly' => 'true',
                'value' => date('H:i'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calEndDt', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'End date of the event',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                'style' => 'width: 100px',
                //'readonly' => 'true',
                'value' => date('d/m/Y'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calEndTm', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'End time of the event',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
                'style' => 'width: 100px',
                'data-mask' => '99:99',
                //'readonly' => 'true',
                'value' => date('H:i'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'note', 
            'attributes' => array(
                'type'  => 'text',
                'class' => ' ',
                //'readonly' => 'true',
                'placeholder'=>'Enter details of the activity'
            ),
            'options' => array(
            ),
        ));
        
        
        
    }
    
}