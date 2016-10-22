<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Trial\Controller\TrialItem' => 'Trial\Controller\TrialItemController',
        ),
    ),
    
    /*'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Trial/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Trial\Entity' => 'application_entities'
            )
     ))), /**/
    
    // The following section is new and should be added to your file
     'router' => array(
         'routes' => array(
             'trial' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-:cid/trial-:tid[/][:action[/]]',
                     'constraints' => array(
                         'cid'     => '[0-9]+',
                         'tid'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Trial\Controller\TrialItem',
                         'action'     => 'index',
                     ),
                 ),
             ),
         ),
     ),
    'view_manager' => array(
        'template_path_stack' => array(
            'trial' => __DIR__ . '/../view',
        ),
    ),
);