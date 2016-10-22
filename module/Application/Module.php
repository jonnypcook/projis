<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Application\Controller\DashboardController;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        // set baseline title
        $app = $e->getParam('application');
        $app->getEventManager()->attach('render', array($this, 'setLayoutTitle'));
        
        $sem  = $eventManager->getSharedManager();
        $sem->attach('application', \Zend\Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'));
        
        $t = $e->getTarget();

        $t->getEventManager()->attach(
            $t->getServiceManager()->get('ZfcRbac\View\Strategy\UnauthorizedStrategy')
        );
    }
    
    public function onDispatch(MvcEvent $e) {
        // leave empty for now
    }


    
    /**
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function setLayoutTitle($e)
    {
        //$matches    = $e->getRouteMatch();
        //$action     = $matches->getParam('action');
        //$controller = $matches->getParam('controller');
        //$module     = __NAMESPACE__;
        $siteName   = '8point3';

        // Getting the view helper manager from the application service manager
        $viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');

        // Getting the headTitle helper from the view helper manager
        $headTitleHelper   = $viewHelperManager->get('headTitle');

        // Setting a separator string for segments
        $headTitleHelper->setSeparator(' | ');

        // Setting the action, controller, module and site name as title segments
        //$headTitleHelper->append(ucwords($action));
        //$headTitleHelper->append($controller);
        //$headTitleHelper->append($module);
        $headTitleHelper->append($siteName);
    }    

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
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
                'GoogleService' => function($sm) {
                    $config = $sm->get('Config');
                    $auth = $sm->get('Zend\Authentication\AuthenticationService');
                    
                    if(!$auth->hasIdentity()) {
                        throw new \Exception('User not found');
                    }
                    
                    return new \Application\Service\GoogleService($config['openAuth2']['google'], $auth->getIdentity(), $sm->get('Doctrine\ORM\EntityManager'));
                }
            ),
            
        );
    }
    
    
}
 
