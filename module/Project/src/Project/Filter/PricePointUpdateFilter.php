<?php
namespace Project\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class PricePointUpdateFilter extends InputFilter
{
	public function __construct()
	{
		// self::__construct(); // parnt::__construct(); - trows and error
		$this->add(array(
            'name'     => 'ppu', // 'usr_name'
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
            'name'     => 'cpu', // 'usr_name'
            'required' => false,
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
            'name'     => 'product', // 'usr_name'
            'required' => true,
            'filters'  => array(),
            'validators' => array(), 
        ));
	}
}