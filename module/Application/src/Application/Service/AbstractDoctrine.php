<?php
namespace Application\Service;

abstract class AbstractDoctrine
{
    protected $objectManager;
    
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
}