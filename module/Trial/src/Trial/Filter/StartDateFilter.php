<?php
namespace Trial\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class StartDateFilter extends InputFilter
{
	public function __construct()
	{
		$this->add(array(
			'name'     => 'installed', // 'usr_name'
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