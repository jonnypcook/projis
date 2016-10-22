<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Assets\Controller\Assets' => 'Assets\Controller\AssetsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'assets' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/assets/:action[/]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Assets\Controller\Assets',
                         'action' => 'index'
                     ),
                 ),
             ),             
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'assets' => __DIR__ . '/../view',
        ),
    ),
);