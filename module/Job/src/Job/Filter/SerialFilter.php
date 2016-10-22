<?php
namespace Job\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SerialFilter extends InputFilter
{
	public function __construct()
	{
		// self::__construct(); // parnt::__construct(); - trows and error
		$this->add(array(
			'name'     => 'products', // 'usr_name'
			'required' => false,
		));
        
        
        $this->add(array(
			'name'     => 'range', // 'usr_name'
			'required' => true,
			'filters'  => array(
			),
            'validators' => array(
                array(
                    'name'    => 'Int',
                ),
                array(
                    'name'    => 'GreaterThan',
                    'options' => array(
                        'min'      => 0,
                        'inclusive' => false
                    ),
                ),
                array(
                    'name'    => 'LessThan',
                    'options' => array(
                        'max'      => 50, // 14 days maximum
                        'inclusive' => true
                    ),
                ),
            ), 
		));
        
        $this->add(array(
			'name'     => 'serialStart', // 'usr_name'
			'required' => true,
			'filters'  => array(
			),
            'validators' => array(
                array(
                    'name'    => 'Int',
                ),
                array(
                    'name'    => 'GreaterThan',
                    'options' => array(
                        'min'      => 0,
                        'inclusive' => false
                    ),
                ),
            ), 
		));

	}
}