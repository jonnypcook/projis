<?php
namespace Task\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class AddTaskActivityFilter extends InputFilter
{
	public function __construct()
	{
		// self::__construct(); // parnt::__construct(); - trows and error
		$this->add(array(
			'name'     => 'note', // 'usr_name'
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
					),
				),
			), 
		));
        
        $this->add(array(
			'name'     => 'duration', // 'usr_name'
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
                        'inclusive' => true
                    ),
                ),
                array(
                    'name'    => 'LessThan',
                    'options' => array(
                        'max'      => 20160, // 14 days maximum
                        'inclusive' => true
                    ),
                ),
            ), 
		));

	}
}