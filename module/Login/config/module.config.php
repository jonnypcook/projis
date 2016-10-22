<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Login\Controller\Doctrine' => 'Login\Controller\DoctrineController',
            'Login\Controller\Google' => 'Login\Controller\GoogleController',
        ),
    ),
    'router' => array (
        'routes' => array (
            'oauth2' => array (
                'type' => 'Literal',
                'options' => array (
                    'route' => '/oauth2google',
                    'defaults' => array (
                        '__NAMESPACE__' => 'Login\Controller',
                        'controller' => 'Doctrine',
                        'action' => 'oauth2google'
                    ),
                ),
                'may_terminate' => true
            ),
            'login' => array (
                'type' => 'Literal',
                'options' => array (
                    'route' => '/login',
                    'defaults' => array (
                        '__NAMESPACE__' => 'Login\Controller',
                        'controller' => 'Doctrine',
                        'action' => 'index'
                    ),
                ),
                'may_terminate' => true
            ),
            'loginbox' => array (
                'type' => 'Literal',
                'options' => array (
                    'route' => '/login/success',
                    'defaults' => array (
                        '__NAMESPACE__' => 'Login\Controller',
                        'controller' => 'Doctrine',
                        'action' => 'success'
                    ),
                ),
                'may_terminate' => true
            ),
            'logout' => array (
                'type' => 'Literal',
                'options' => array (
                    'route' => '/logout',
                    'defaults' => array (
                        '__NAMESPACE__' => 'Login\Controller',
                        'controller' => 'Doctrine',
                        'action' => 'logout'
                    ),
                ),
                'may_terminate' => true
            )
        )
    ),
    'view_manager' => array(
        'template_map' => array(
            'login/layout'           => __DIR__ . '/../view/layout/layout.phtml',
        ),/**/
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'display_exceptions'       => true,
    ),
    'doctrine' => array(
		// 1) for Aithentication
        'authentication' => array( // this part is for the Auth adapter from DoctrineModule/Authentication
            'orm_default' => array(
                'object_manager' => 'Doctrine\ORM\EntityManager',
				// object_repository can be used instead of the object_manager key
                'identity_class' => '\Application\Entity\User', //'Application\Entity\User',
                'identity_property' => 'username', // 'username', // 'email',
                'credential_property' => 'password', // 'password',
                'credential_callable' => function(\Application\Entity\User $user, $passwordGiven) { // not only User
                    // return my_awesome_check_test($user->getPassword(), $passwordGiven);
					// echo '<h1>callback user->getPassword = ' .$user->getPassword() . ' passwordGiven = ' . $passwordGiven . '</h1>';
					//- if ($user->getPassword() == md5($passwordGiven)) { // original
					// ToDo find a way to access the Service Manager and get the static salt from config array
					if ($user->getPassword() == md5('aFGQ475SDsdfsaf2342' . $passwordGiven . $user->getPasswordSalt()) &&
						$user->getActive() == 1) {
						return true;
					}
					else {
						return false;
					}
                },
            ),
        ),
    ),
);