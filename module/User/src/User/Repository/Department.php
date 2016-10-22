<?php

namespace User\Repository;

use Doctrine\ORM\EntityRepository;
use User\Entity;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class Department extends EntityRepository
{
}

