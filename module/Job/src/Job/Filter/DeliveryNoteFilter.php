<?php
namespace Job\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DeliveryNoteFilter extends InputFilter
{
	public function __construct()
	{
		$this->add(array(
			'name'     => 'sent', // 'usr_name'
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
        
        $this->add(array(
			'name'     => 'address', // 'usr_name'
			'required' => false,
			'filters'  => array(),
            'validators' => array(), 
        ));
        
        $this->add(array(
			'name'     => 'reference', // 'usr_name'
			'required' => false,
			'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
            'validators' => array(), 
        ));
        
        
	}
}