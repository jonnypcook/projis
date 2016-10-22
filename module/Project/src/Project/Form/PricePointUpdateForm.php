<?php
namespace Project\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class PricePointUpdateForm extends Form 
{
    public function __construct()
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setAttribute('method', 'post');
        
        $pcOcc = array();
        $pcLux = array();
        for ($i=0; $i<=18; $i++) {
            $pcOcc[($i*5)] = ($i*5).'%'.((($i*5)==30)?' (default)':'');
            $pcLux[($i*5)] = ($i*5).'%'.((($i*5)==40)?' (default)':'');
        }

        $this->add(array(
            'name' => 'ppu', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Price per unit of the product to the client',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'cpu', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Cost per unit of the product to the vendor',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'product', // 'usr_name',
            'attributes' => array(
                'type'  => 'hidden',
            ),
            'options' => array(
            ),
        ));
        
    }
    
}