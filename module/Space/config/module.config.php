<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Space\Controller\SpaceItem' => 'Space\Controller\SpaceItemController',
        ),
    ),
    
    'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Space/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Space\Entity' => 'application_entities'
            )
     ))), 
    
    // The following section is new and should be added to your file
     'router' => array(
         'routes' => array(
             'space' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-[:cid]/project-[:pid]/space-[:sid][/][:action[/]]',
                     'constraints' => array(
                         'cid'     => '[0-9]+',
                         'pid'     => '[0-9]+',
                         'sid'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Space\Controller\SpaceItem',
                         'action'     => 'index',
                     ),
                 ),
             ),
         ),
     ),
      
    
    'view_manager' => array(
        'template_path_stack' => array(
            'space' => __DIR__ . '/../view',
        ),
    ),
);