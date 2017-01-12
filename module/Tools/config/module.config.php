<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Tools\Controller\Tools' => 'Tools\Controller\ToolsController',
            'Tools\Controller\Emergency' => 'Tools\Controller\EmergencyController',
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

    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'list-users' => array(
                    'options' => array(
                        'route'    => 'emergency [rbs|all|non-rbs]:mode [--verbose|-v] [-s|--synchronize] [-t|--test]',
                        'defaults' => array(
                            'controller' => 'Tools\Controller\Emergency',
                            'action'     => 'emergency'
                        )
                    )
                )
            ),
        ),
    ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            'tools' => __DIR__ . '/../view',
        ),
    ),
);