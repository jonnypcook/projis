<?php
namespace Project\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Project\Service\Model as Model;

class ModelFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return \MyNamespace\Service\SomeService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new Model();
        $service->setEntityManager($serviceLocator->get('Doctrine\ORM\EntityManager'));
        return $service;
    }
}