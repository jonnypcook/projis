<?php
namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class DebugPlugin extends AbstractPlugin
{
    public function dump($type, $die=true) {
        echo '<pre>', print_r($type, true), '</pre>'; 
        if ($die) {
            die();
        }
    }
}