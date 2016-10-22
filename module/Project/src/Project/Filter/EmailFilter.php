<?php
namespace Project\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class EmailFilter extends InputFilter
{
	public function __construct()
	{
		// self::__construct(); // parnt::__construct(); - trows and error
		$this->add(array(
			'name'     => 'to', // 'usr_name'
			'required' => true,
			
		));

		$this->add(array(
			'name'     => 'cc', // 'usr_name'
			'required' => false,
			
		));


		$this->add(array(
			'name'     => 'subject', // 'usr_name'
			'required' => true,
			'filters'  => array(
				array('name' => 'StripTags'),
			),
		));

		$this->add(array(
			'name'     => 'message', // 'usr_name'
			'required' => true,
			'filters'  => array(
				array('name' => 'StripTags'),
			),
		));



	}
}