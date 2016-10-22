<?php
namespace User\Form;

use Zend\Form\Form;


class ChangePasswordForm extends Form 
{
    public function __construct()
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        
        $this->add(array(
            'type'  => 'password',
            'name' => 'password', // 'usr_name',
            'attributes' => array(
                'data-original-title' => 'Enter your current password',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'type'  => 'password',
            'name' => 'newPassword', // 'usr_name',
            'attributes' => array(
                'data-original-title' => 'Enter a new password - please ensure that the password is at least 8 characters long and contains at least one number',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'type'  => 'password',
            'name' => 'newPasswordConfirm', // 'usr_name',
            'attributes' => array(
                'data-original-title' => 'Re-type your new password',
                'data-trigger' => 'hover',
                'class' => 'span6  tooltips',
            ),
            'options' => array(
            ),
        ));
        
        
        
    }
}