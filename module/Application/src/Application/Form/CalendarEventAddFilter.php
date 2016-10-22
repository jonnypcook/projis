<?php
namespace Application\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CalendarEventAddFilter extends InputFilter
{
	public function __construct()
	{
		// self::__construct(); // parnt::__construct(); - trows and error
		$this->add(array(
			'name'     => 'title', // 'usr_name'
			'required' => true,
			'filters'  => array(
				array('name' => 'StripTags'),
			),
			'validators' => array(
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'min'      => 1,
						'max'      => 512,
					),
				),
			), 
		));

        $this->add(array(
			'name'     => 'calStartDt', // 'usr_name'
			'required' => true,
			'filters'  => array(
			),
			'validators' => array(
				array(
					'name'    => '\Zend\Validator\Date',
					'options' => array(
						'format' => 'd/m/Y',
					),
				),
			), 
		));

        $this->add(array(
			'name'     => 'calStartTm', // 'usr_name'
			'required' => false,
			'filters'  => array(
			),
			'validators' => array(
				array(
					'name'    => '\Zend\Validator\Date',
					'options' => array(
						'format' => 'H:i',
					),
				),
			), 
		));

        $this->add(array(
			'name'     => 'calEndDt', // 'usr_name'
			'required' => true,
			'filters'  => array(
			),
			'validators' => array(
				array(
					'name'    => '\Zend\Validator\Date',
					'options' => array(
						'format' => 'd/m/Y',
					),
				),
			), 
		));

        $this->add(array(
			'name'     => 'calEndTm', // 'usr_name'
			'required' => false,
			'filters'  => array(
			),
			'validators' => array(
				array(
					'name'    => '\Zend\Validator\Date',
					'options' => array(
						'format' => 'H:i',
					),
				),
			), 
		));
        
        // advanced options
        $this->add(array(
			'name'     => 'location', // 'usr_name'
			'required' => false,
			'filters'  => array(
				array('name' => 'StripTags'),
			),
			'validators' => array(
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'min'      => 1,
						'max'      => 512,
					),
				),
			), 
		));
    
        $this->add(array(
			'name'     => 'allday', // 'usr_name'
			'required' => false,
		));
        
        $this->add(array(
			'name'     => 'users', // 'usr_name'
			'required' => false,
		));

        $this->add(array(
			'name'     => 'usersBespoke', // 'usr_name'
			'required' => false,
		));

        $this->add(array(
			'name'     => 'sendNotifications', // 'usr_name'
			'required' => false,
		));


	}
}