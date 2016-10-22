<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\View\Model\JsonModel;


class PlaygroundController extends AuthController
{
    public function indexAction()
    {
        $this->setCaption('Playground');
        
        /*$this->getView()
                ->setVariable('count', count($projects))
                ->setVariable('term', $term)
                ->setVariable('projects', $projects);/**/

        return $this->getView();
    }
    
    public function routemappingAction() {
        $this->setCaption('Google Route Mapping Demo');
        
        /*$this->getView()
                ->setVariable('count', count($projects))
                ->setVariable('term', $term)
                ->setVariable('projects', $projects);/**/

        return $this->getView();
    }
    
    public function colourdimmingAction() {
        $this->setCaption('Colour Dimming Demo');
        
        return $this->getView();
    }
    
    public function hmrcAction() {
        $this->setCaption('HM Prisons Demo');
        
        return $this->getView();
    }
    
     
}
