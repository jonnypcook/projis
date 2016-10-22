<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'controller_plugins' => array(
            'invokables' => array(
              'auditPlugin' => 'Application\Plugin\AuditPlugin',
              'debug' => 'Application\Plugin\DebugPlugin',
        )
    ),
    'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Application/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Application\Entity' => 'application_entities'
            )
     ))),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Dashboard',
                        'action'     => 'index',
                    ),
                ),
            ),
            'dashboard' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/dashboard/:action[/]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Application\Controller\Dashboard',
                     ),
                 ),
             ),

            'calendar' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/calendar[/:action][/]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Application\Controller\Calendar',
                         'action'   => 'index'
                     ),
                 ),
             ),

            'activity' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/activity[/][:action[/]]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Application\Controller\Activity',
                         'action'   => 'index'
                     ),
                 ),
             ),

            'competitor' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/competitor/:action[/]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Application\Controller\Competitor',
                     ),
                 ),
             ),

            'search' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/search[/:action][/]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Application\Controller\Search',
                         'action'   => 'index'
                     ),
                 ),
             ),

            'playground' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/playground[/:action][/]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Application\Controller\Playground',
                         'action'   => 'index'
                     ),
                 ),
             ),

            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action

        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory', // <-- add this
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Dashboard' => 'Application\Controller\DashboardController',
            'Application\Controller\Calendar' => 'Application\Controller\CalendarController',
            'Application\Controller\Search' => 'Application\Controller\SearchController',
            'Application\Controller\Playground' => 'Application\Controller\PlaygroundController',
            'Application\Controller\Competitor' => 'Application\Controller\CompetitorController',
            'Application\Controller\Activity' => 'Application\Controller\ActivityController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/dashboard/index' => __DIR__ . '/../view/application/dashboard/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/403'               => __DIR__ . '/../view/error/403.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
	'view_helpers' => array(
        'invokables' => array(
            'PaginationHelper' => 'Application\View\Helper\PaginationHelper'
        )
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    // create the navigation
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Dashboard',
                'route' => 'home',
                'ico'=> 'icon-dashboard',
                'pages' => array (
                    array(
                        'skip' => true,
                        'label' => 'Activity',
                        'route' => 'activity',
                    )
                )
            ),
            array(
                'label' => 'Clients',
                'route' => 'clients',
                'permissions' => array('client.read', 'project.read'),
                'ico'=> 'icon-book',
                'pages' => array(
                    array(
                        'label' => 'Clients',
                        'route' => 'clients',
                        'permissions' => array('client.read'),
                    ),
                    array(
                        'label' => 'Projects',
                        'route' => 'projects',
                        'permissions' => array('project.read'),
                    ),
                    array(
                        'label' => 'Jobs',
                        'route' => 'jobs',
                        'permissions' => array('project.read'),
                    ),
                ),
            ),
            array(
                'label' => 'Products',
                'route' => 'product',
                'ico'=> 'icon-tags',
                'permissions' => array('product.read'),
                'pages' => array(
                    array(
                        'label' => 'Catalogue',
                        'route' => 'product',
                        'action' => 'catalog',
                    ),
                    array(
                        'label' => 'Philips',
                        'route' => 'product',
                        'action' => 'philips',
                    ),
                    array(
                        'label' => 'Image Gallery',
                        'route' => 'product',
                        'action' => 'gallery',
                    ),
                    array(
                        'label' => 'Reporting',
                        'route' => 'product',
                        'action'=>'reporting'
                    ),
                    /*array(
                        'label' => 'Batch-Control',
                        'route' => 'product',
                        'action'=>'catalog'
                    ),
                    array(
                        'label' => 'Components',
                        'route' => 'product',
                        'action'=>'catalog'
                    ),/**/
                ),
            ),
            array(
                'label' => 'Legacy',
                'route' => 'legacy',
                'ico'=> 'icon-undo',
                'permissions' => array('legacy.read'),
            ),
            array(
                'label' => 'Contacts',
                'route' => 'contactbook',
                'ico'=> 'icon-book',
                'permissions' => array('contact.read'),
            ),
            array(
                'label' => 'Tasks',
                'route' => 'tasks',
                'ico'=> 'icon-tasks',
                'permissions' => array('task.read'),
                'pages' => array(
                    array(
                        'label' => 'Task Manager',
                        'route' => 'tasks',
                        'action' => 'index',
                    ),
                    array(
                        'label' => 'Development Tasks',
                        'route' => 'tasks',
                        'action' => 'development',
                    ),
                ),
            ),
            array(
                'label' => 'Reporting',
                'route' => 'reports',
                'ico'=> 'icon-th',
                'pages' => array(
                    array(
                        'label' => 'Dashboard',
                        'route' => 'reports',
                        'action' => 'index',
                        'skip'  => true,
                    ),
                    array(
                        'label' => 'Run Report',
                        'route' => 'reports',
                        'action' => 'view',
                        'skip'  => true,
                    ),
                ),
            ),
            array(
                'label' => 'Targets',
                'route' => 'targets',
                'ico'=> 'icon-bullseye',
                'pages' => array(
                    array(
                        'label' => 'Targets',
                        'route' => 'targets',
                        'action' => 'index',
                        'skip'  => true,
                    ),
                ),
            ),
            array(
                'label' => 'Tracking',
                'route' => 'assets',
                'ico'=> 'icon-fire',
                'pages' => array(
                    array(
                        'label' => 'Installers\' Barcode',
                        'route' => 'assets',
                        'action' => 'installers',
                        'skip'  => true,
                    ),
                    array(
                        'label' => 'Batch-Control',
                        'route' => 'assets',
                        'action'=>'batchcontrol',
                        'skip'  => true,
                    ),
                    array(
                        'label' => 'Components',
                        'route' => 'assets',
                        'action'=>'components',
                        'skip'  => true,
                    ),/**/
                )
            ),
            array(
                'label' => 'Tools',
                'route' => 'tools',
                'ico'=> 'icon-laptop',
                //'permissions' => array('product.read'),
                'pages' => array(
                    array(
                        'label' => 'Dashboard',
                        'route' => 'tools',
                    ),
                    array(
                        'label' => 'Remote Phosphor',
                        'route' => 'tools',
                        'action' => 'rpcalculator',
                    ),
                ),
            ),
            array(
                'label' => 'Calendar',
                'route' => 'calendar',
                'ico'=> 'icon-calendar',
                'pages' => array(
                    array(
                        'label' => 'Add Advanced Event',
                        'route' => 'calendar',
                        'action' => 'advancedevent',
                        'skip'=>true,
                    ),
                    array(
                        'label' => 'View Event',
                        'route' => 'calendar',
                        'action' => 'advancededit',
                        'skip'=>true,
                    ),
                )
            ),
            array(
                'label' => 'Search',
                'route' => 'search',
                'ico'=> 'icon-search',
            ),
            array(
                'label' => 'Administration',
                'route' => 'login',
                'ico'=> 'icon-cog',
                'pages' => array(
                    array(
                        'label' => 'User Profile',
                        'route' => 'user',
                        'action' => 'profile'
                    ),
                    array(
                        'label' => 'Change Password',
                        'route' => 'user',
                        'action' => 'password'
                    ),
                    array(
                        'label' => 'Resources',
                        'route' => 'resource_activities',
                        'action' => 'index'
                    )
                ),
            ),
            array(
                'label' => 'Playground',
                'route' => 'playground',
                'permissions' => array('admin.playground'),
                'ico'=> 'icon-play',
            ),
            array(
                'label' => 'Logout',
                'route' => 'logout',
                'ico'=> 'icon-user',
            ),
        ),
    ),
);
