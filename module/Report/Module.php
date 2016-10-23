<?php
namespace Report;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Model' => 'Project\Factory\ModelFactory',
                'DocumentService' => function($sm) {
                    $config = $sm->get('Config');
                    return new DocumentService($config['googleApps']['drive']['location'], $sm->get('Doctrine\ORM\EntityManager'));
                }
            ),

        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}