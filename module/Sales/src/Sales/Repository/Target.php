<?php

namespace Sales\Repository;

use Doctrine\ORM\EntityRepository;
use Sales\Entity;

class Target extends EntityRepository
{
    public function findUsersForSalesMarginTarget()
    {
        $qb    = $this->_em->createQueryBuilder();
        $query = $qb->select( 's' )
            ->from( 'Sales\Entity\SalesTarget', 's' )
            ->join( 's.user', 'u' );

        return $query->getQuery()->getResult();
    }

    public function findTarget( $user = null )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('s, u')
            ->from('Sales\Entity\Target', 's')
            ->join('s.user', 'u')
            ->orderBy('u.department', 'ASC');

        if ( !empty($user) )
        {
            $qb->where('u.userId = ?1')
                ->setParameter(1, $user);
        }

        $targets = $qb->getQuery()->getResult();

        $result = [];
        foreach($targets as $target)
        {
            $result[$target->getUser()->getUserId()] = Array(
                'user' => $target->getUser()->getUserId(),
                'user_name' => $target->getUser()->getForename() . ' ' . $target->getUser()->getSurname(),
                'department' => $target->getUser()->getDepartment()->getDepartmentId(),
                'department_name' => $target->getUser()->getDepartment()->getName(),
                'sales' => Array(
                    1 => $target->getSalesMonth1(),
                    2 => $target->getSalesMonth2(),
                    3 => $target->getSalesMonth3(),
                    4 => $target->getSalesMonth4(),
                    5 => $target->getSalesMonth5(),
                    6 => $target->getSalesMonth6(),
                    7 => $target->getSalesMonth7(),
                    8 => $target->getSalesMonth8(),
                    9 => $target->getSalesMonth9(),
                    10 => $target->getSalesMonth10(),
                    11 => $target->getSalesMonth11(),
                    12 => $target->getSalesMonth12()
                ),
                'margin' => Array(
                    1 => $target->getMarginMonth1(),
                    2 => $target->getMarginMonth2(),
                    3 => $target->getMarginMonth3(),
                    4 => $target->getMarginMonth4(),
                    5 => $target->getMarginMonth5(),
                    6 => $target->getMarginMonth6(),
                    7 => $target->getMarginMonth7(),
                    8 => $target->getMarginMonth8(),
                    9 => $target->getMarginMonth9(),
                    10 => $target->getMarginMonth10(),
                    11 => $target->getMarginMonth11(),
                    12 => $target->getMarginMonth12()
                ),
                'year' => $target->getYear(),
            );
        }

        return $result;
    }

    public function findQuarterlyTarget( $user = null )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('s, u')
            ->from('Sales\Entity\Target', 's')
            ->join('s.user', 'u');

        if ( !empty($user) )
        {
            $qb->where('u.userId = ?1')
                ->setParameter(1, $user);
        }

        $targets = $qb->getQuery()->getResult();

        $result = [];
        foreach($targets as $target)
        {
            $result[$target->getUser()->getUserId()] = Array(
                'user' => $target->getUser()->getUserId(),
                'user_name' => $target->getUser()->getForename() . ' ' . $target->getUser()->getSurname(),
                'department' => $target->getUser()->getDepartment()->getDepartmentId(),
                'department_name' => $target->getUser()->getDepartment()->getName(),
                'sales' => Array(
                    1=> $target->getSalesMonth1() + $target->getSalesMonth2() + $target->getSalesMonth3(),
                    2 => $target->getSalesMonth4() + $target->getSalesMonth5() + $target->getSalesMonth6(),
                    3 => $target->getSalesMonth7() + $target->getSalesMonth8() + $target->getSalesMonth9(),
                    4 => $target->getSalesMonth10() + $target->getSalesMonth11() + $target->getSalesMonth12(),
                ),
                'margin' => Array(
                    1 => $target->getMarginMonth1() + $target->getMarginMonth2() + $target->getMarginMonth3(),
                    2 => $target->getMarginMonth4() + $target->getMarginMonth5() + $target->getMarginMonth6(),
                    3 => $target->getMarginMonth7() + $target->getMarginMonth8() + $target->getMarginMonth9(),
                    4 => $target->getMarginMonth10() + $target->getMarginMonth11() + $target->getMarginMonth12(),
                ),
                'year' => $target->getYear(),
            );
        }

        return $result;
    }
}

