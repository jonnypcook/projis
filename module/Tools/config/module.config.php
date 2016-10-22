<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Tools\Controller\Tools' => 'Tools\Controller\ToolsController',
        ),
    ),
    
    'router' => array(
        'routes' => array(
           'tools' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/tools[/][:action[/]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Tools\Controller\Tools',
                        'action'     => 'index',
                    ),
                ),
            ),             
        ),
     ),
    
    
    'view_manager' => array(
        'template_path_stack' => array(
            'tools' => __DIR__ . '/../view',
        ),
    ),
);