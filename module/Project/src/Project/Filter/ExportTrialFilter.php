<?php
namespace Project\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ExportTrialFilter extends InputFilter
{
	public function __construct()
	{
		// self::__construct(); // parnt::__construct(); - trows and error
		$this->add(array(
            'name'     => 'name', // 'usr_name'
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 1,
                        'max'      => 255,
                    ),
                ),
            ), 
		));

		$this->add(array(
            'name'     => 'installation', // 'usr_name'
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
            'name'     => 'delivery', // 'usr_name'
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


	}
}