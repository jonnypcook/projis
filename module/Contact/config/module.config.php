<?php
return array(
    'controllers' => array(
         'invokables' => array(
             'Contact\Controller\Contact' => 'Contact\Controller\ContactController',
         ),
     ),/**/
    
    'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Contact/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Contact\Entity' => 'application_entities'
            )
     ))), 


    // The following section is new and should be added to your file
    'router' => array(
         'routes' => array(
            'contactbook' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/contact[/:action][/]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Contact\Controller\Contact',
                         'action'     => 'index',
                     ),
                 ),
             ),
         ),
     ),/**/
    
    'view_manager' => array(
       'template_path_stack' => array(
            'contact' => __DIR__ . '/../view',
        ),
    ),
    
);