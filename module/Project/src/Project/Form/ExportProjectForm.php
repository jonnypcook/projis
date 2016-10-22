<?php
namespace Project\Form;

use Zend\Form\Form;


class ExportProjectForm extends Form 
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
                'class' => 'span12  ',
            ),
            'options' => array(
            ),
        ));
        
    }
    
}