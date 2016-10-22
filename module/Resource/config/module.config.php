<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Resource\Controller\Activity' => 'Resource\Controller\ActivityController',
            'Resource\Controller\ActivityItem' => 'Resource\Controller\ActivityItemController',
            'Resource\Controller\Resource' => 'Resource\Controller\ResourceController',
            'Resource\Controller\ResourceItem' => 'Resource\Controller\ResourceItemController',
            'Resource\Controller\CostCode' => 'Resource\Controller\CostCodeController',
            'Resource\Controller\CostCodeItem' => 'Resource\Controller\CostCodeItemController',

        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Resource/Entity')
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Resource\Entity' => 'application_entities'
                )
            ))),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'resource_activities' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/resource-activities[/:action][/job-:jid][/page-:page][/]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Resource\Controller\Activity',
                        'action'     => 'index',
                    ),
                ),
            ),
            'resource_activity' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/resource-activity/resource-:rid[/][:action[/]]',
                    'constraints' => array(
                        'rid'     => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Resource\Controller\ActivityItem',
                        'action'     => 'index',
                    ),
                ),
            ),
            'resource_items' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/resource-items[/:action][/]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Resource\Controller\Resource',
                        'action'     => 'index',
                    ),
                ),
            ),
            'resource_item' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/resource-item/resource-:rid[/][:action[/]]',
                    'constraints' => array(
                        'rid'     => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Resource\Controller\ResourceItem',
                        'action'     => 'index',
                    ),
                ),
            ),
            'cost_codes' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/cost-codes[/:action][/]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Resource\Controller\CostCode',
                        'action'     => 'index',
                    ),
                ),
            ),
            'cost_code_item' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/cost-code/item-:ccid[/][:action[/]]',
                    'constraints' => array(
                        'ccid'     => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Resource\Controller\CostCodeItem',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'Resource' => __DIR__ . '/../view',
        ),
    ),
);