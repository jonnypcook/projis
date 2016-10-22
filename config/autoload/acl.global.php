<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array (
    'acl' => array(
        'roles' => array (
            'guest'=>null,
            'member' => 'guest',
            'admin' => 'member',
        ),
        'resources' => array (
            'allow' => array (
                'Login\Controller\Doctrine' => array(
                    'all'   => 'guest'
                ),
                'Application\Controller\Dashboard' => array (
                    'index'=>array('member', 'member')
                )
            )
        )/**/
    )
);