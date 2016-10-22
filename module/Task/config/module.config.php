<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Task\Controller\Task' => 'Task\Controller\TaskController',
            'Task\Controller\TaskItem' => 'Task\Controller\TaskItemController',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Task/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Task\Entity' => 'application_entities'
            )
     ))), 
    
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
           'tasks' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/task[/][:action[/]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Task\Controller\Task',
                        'action'     => 'index',
                    ),
                ),
            ),             
            'task' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/task-:tid[/:action][/]',
                    'constraints' => array(
                        'tid'     => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Task\Controller\TaskItem',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
     ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            'task' => __DIR__ . '/../view',
        ),
    ),
);