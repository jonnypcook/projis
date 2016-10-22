<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Report\Controller\Report' => 'Report\Controller\ReportController',
            'Report\Controller\ReportList' => 'Report\Controller\ReportListController',
            'Report\Controller\ReportTotal' => 'Report\Controller\ReportTotalController',
            'Report\Controller\ReportExport' => 'Report\Controller\ReportExportController',
            'Report\Controller\ReportAll' => 'Report\Controller\ReportAllController',
            'Report\Controller\ReportQuarterly' => 'Report\Controller\ReportQuarterlyController',
            'Report\Controller\ReportRating' => 'Report\Controller\ReportRatingController',
            'Report\Controller\OrderBook' => 'Report\Controller\OrderBookController',
        ),
    ),
    
    'doctrine' => array(
        'driver' => array(
          'application_entities' => array(
            'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(__DIR__ . '/../src/Report/Entity')
          ),

          'orm_default' => array(
            'drivers' => array(
              'Report\Entity' => 'application_entities'
            )
     ))), 
    
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
           'reports' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/report[/:action][/:group][/:report]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Report\Controller\Report',
                        'action'     => 'index',
                    ),
                ),
            ),
           'reportlist' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportlist[/:action][/:type][/:month][/:year][/:user]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportList',
                       'action'     => 'index',
                   ),
               ),
           ),
           'totalreport' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportall[/:action][/:type][/:user]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportTotal',
                       'action'     => 'index',
                   ),
               ),
           ),
           'alltotalreport' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportalltotal[/:action][/:type][/:user]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportAll',
                       'action'     => 'index',
                   ),
               ),
           ),
           'reportexport' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportexport[/:action][/:type][/:month][/:year][/:user]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportExport',
                       'action'     => 'index',
                   ),
               ),
           ),
           'exportproducts' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportproduct[/:action][/:report][/:product_id]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\Report',
                       'action'     => 'index',
                   ),
               ),
           ),
           'reportquarterly' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportquarterly[/:action][/quarter-:quarter][/year-:year][/export-:export]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportQuarterly',
                       'action'     => 'index',
                   ),
               ),
           ),
           'ratingreport' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/ratingreport[/:action][/rating-:rating][/user-:user][/month-:month]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportRating',
                       'action'     => 'index',
                   ),
               ),
           ),
           'ratingchart' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/ratingchart[/:action][/:user]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportRating',
                       'action'     => 'index',
                   ),
               ),
           ),
           'yearlyoutlook' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportyearlyoutlook[/:action][/:user][/type-:type]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\ReportRating',
                       'action'     => 'index',
                   ),
               ),
           ),
           'reportorderbook' => array(
               'type'    => 'segment',
               'options' => array(
                   'route'    => '/reportorderbook[/:action][/:year][/:month]',
                   'constraints' => array(
                       'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                   ),
                   'defaults' => array(
                       'controller' => 'Report\Controller\OrderBook',
                       'action'     => 'index',
                   ),
               ),
           ),
        ),
     ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            'report' => __DIR__ . '/../view',
        ),
    ),
);