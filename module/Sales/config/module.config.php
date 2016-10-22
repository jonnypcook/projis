<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Sales\Controller\Target' => 'Sales\Controller\TargetController',
            'Sales\Controller\TargetItem' => 'Sales\Controller\TargetItemController',
        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Sales/Entity')
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Sales\Entity' => 'application_entities'
                )
            ))),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'targets' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/targets[/:action][/]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Sales\Controller\Target',
                        'action'     => 'index',
                    ),
                ),
            ),
            'target' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/target-:id[/:action][/]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Sales\Controller\TargetItem',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'sales' => __DIR__ . '/../view',
        ),
    ),
);