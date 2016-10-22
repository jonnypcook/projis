<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Job\Controller\JobItem' => 'Job\Controller\JobItemController',
            'Job\Controller\Job' => 'Job\Controller\JobController',
        ),
    ),
    
    'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Job/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Job\Entity' => 'application_entities'
            )
     ))), 
    
     // The following section is new and should be added to your file
     'router' => array(
         'routes' => array(
             'jobs' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/job[/][:action[/]]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Job\Controller\Job',
                         'action'     => 'index',
                     ),
                 ),
             ),  
             'job' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-:cid/job-:jid[/][:action[/]]',
                     'constraints' => array(
                         'cid'     => '[0-9]+',
                         'jid'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Job\Controller\JobItem',
                         'action'     => 'index',
                     ),
                 ),
             ),
             'jobdocument' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-:cid/job-:jid/document/:action[/]',
                     'constraints' => array(
                         'cid'     => '[0-9]+',
                         'jid'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Job\Controller\JobItem',
                         'action'     => 'index',
                     ),
                 ),
             ),

         ),
     ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            'job' => __DIR__ . '/../view',
        ),
    ),
);