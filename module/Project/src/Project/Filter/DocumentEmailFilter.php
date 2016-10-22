<?php
namespace Project\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DocumentEmailFilter extends InputFilter
{
	public function __construct()
	{
		// self::__construct(); // parnt::__construct(); - trows and error
		$this->add(array(
			'name'     => 'emailRecipient', // 'usr_name'
			'required' => true,
			'filters'  => array(
				array('name' => 'StripTags'),
			),
			'validators' => array(
				array(
					'name'    => 'emailAddress',
				),
			), 
		));


		$this->add(array(
			'name'     => 'emailSubject', // 'usr_name'
			'required' => true,
			'filters'  => array(
				array('name' => 'StripTags'),
			),
		));

		$this->add(array(
			'name'     => 'emailMessage', // 'usr_name'
			'required' => true,
			'filters'  => array(
				array('name' => 'StripTags'),
			),
		));



	}
}