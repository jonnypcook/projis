<?php
namespace Product\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ProductPhilipsFilter extends InputFilter
{
	public function __construct()
	{
		$this->add(array(
			'name'     => 'pwr', // 'usr_name'
			'required' => true,
			'filters'  => array(),
            'validators' => array(
                array(
                    'name'    => '\Zend\I18n\Validator\Float',
                ),
                array(
                    'name'    => 'GreaterThan',
                    'options' => array(
                        'min'      => 0,
                        'inclusive' => true
                    ),
                ),
            ), 
        ));
        
        $this->add(array(
			'name'     => 'description', // 'usr_name'
			'required' => true,
			'filters'  => array(),
            'validators' => array(), 
        ));
        
	}
}