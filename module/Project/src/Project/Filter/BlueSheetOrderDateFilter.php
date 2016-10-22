<?php
namespace Project\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class BlueSheetOrderDateFilter extends InputFilter
{
	public function __construct()
	{
		$this->add(array(
			'name'     => 'OrderDate', // 'usr_name'
			'required' => true,
			'filters'  => array(),
            'validators' => array(
				array(
					'name'    => '\Zend\Validator\Date',
					'options' => array(
						'format' => 'd/m/Y',
					),
				),
			), 
        ));

	}
}