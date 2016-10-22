<?php
namespace User\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ChangePasswordFilter extends InputFilter
{
	public function __construct()
	{
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
		));
        
        $this->add(array(
            'name' => 'newPassword',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 8,
                        'max' => 32
                    )
                )
            )
		));

        $this->add(array(
			'name' => 'newPasswordConfirm',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 32
                    ),
                ),
                array(
                    'name' => 'Identical',
                    'options' => array(
                        'token' => 'newPassword' //I have tried $_POST['password'], but it doesnt work either
                    )
                )
            )
		));

	}
}