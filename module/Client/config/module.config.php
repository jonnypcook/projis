<?php
return array(
    'controllers' => array(
         'invokables' => array(
             'Client\Controller\Client' => 'Client\Controller\ClientController',
             'Client\Controller\ClientItem' => 'Client\Controller\ClientItemController',
             'Client\Controller\Building' => 'Client\Controller\BuildingController',
             'Client\Controller\BuildingItem' => 'Client\Controller\BuildingItemController',
             'Client\Controller\Contact' => 'Client\Controller\ContactController',
         ),
     ),
    
    'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Client/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Client\Entity' => 'application_entities'
            )
     ))), 


    // The following section is new and should be added to your file
     'router' => array(
         'routes' => array(
            'clients' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client[/][:action[/]]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Client\Controller\Client',
                         'action'     => 'index',
                     ),
                 ),
             ),             
             'client' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-[:id][/][:action[/]]',
                     'constraints' => array(
                         'id'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Client\Controller\ClientItem',
                         'action'     => 'index',
                     ),
                 ),
             ),             
             'buildings' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-[:id][/]building[/][:action[/]]',
                     'constraints' => array(
                         'id'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Client\Controller\Building',
                         'action'     => 'index',
                     ),
                 ),
             ),
             'building' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-[:id]/building-[:bid][/][:action[/]]',
                     'constraints' => array(
                         'id'     => '[0-9]+',
                         'bid'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Client\Controller\BuildingItem',
                         'action'     => 'index',
                     ),
                 ),
             ),
             'contacts' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-[:id]/contact[/][:action[/]]',
                     'constraints' => array(
                         'id'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Client\Controller\Contact',
                         'action'     => 'index',
                     ),
                 ),
             ),
             'contact' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/client-[:id]/contact-[:cid][/][:action[/]]',
                     'constraints' => array(
                         'id'     => '[0-9]+',
                         'cid'     => '[0-9]+',
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Client\Controller\Contact',
                         'action'     => 'item',
                     ),
                 ),
             ),
         ),
     ),
    
    'view_manager' => array(
       'template_path_stack' => array(
            'client' => __DIR__ . '/../view',
        ),
    ),
    
);