<?php

namespace Sales\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Controller\AuthController;

use Zend\Mvc\MvcEvent;

use Sales\Form;

class TargetController extends AuthController
{

    public function indexAction()
    {
        $this->setCaption( 'Target - View Targets' );
        $em = $this->getEntityManager();

        //$projects = $em->getRepository('Project\Entity\Project')->findByUserId();

        $data = $em->getRepository( 'Sales\Entity\Target' )->findAll();

        $targets = [ ];
        if ( !empty($data) )
        {
            foreach ( $data as $item )
            {
                $sales = $item->getSalesMonth1() + $item->getSalesMonth2() + $item->getSalesMonth3() + $item->getSalesMonth4()
                    + $item->getSalesMonth5() + $item->getSalesMonth6() + $item->getSalesMonth7() + $item->getSalesMonth8()
                    + $item->getSalesMonth9() + $item->getSalesMonth10() + $item->getSalesMonth11() + $item->getSalesMonth12();

                $margin     = $item->getMarginMonth1() + $item->getMarginMonth2() + $item->getMarginMonth3() + $item->getMarginMonth4()
                    + $item->getMarginMonth5() + $item->getMarginMonth6() + $item->getMarginMonth7() + $item->getMarginMonth8()
                    + $item->getMarginMonth9() + $item->getMarginMonth10() + $item->getMarginMonth11() + $item->getMarginMonth12();
                $department = $item->getUser()->getDepartment();
                $arrId      = !empty($department) ? $department->getDepartmentId() : 'other';

                $targets[$arrId][] = Array(
                    'target_id'         => $item->getTargetId(),
                    'user_id'           => $item->getUser()->getUserId(),
                    'user'              => $item->getUser()->getForename() . ' ' . $item->getUser()->getSurname(),
                    'year'              => $item->getYear(),
                    'sales'             => $sales,
                    'margin'            => $margin,
                    'margin_percentage' => ($margin / $sales) * 100,
                    'department'        => !empty($department) ? $department->getName() : 'None',
                    'jobs'              => $em->getRepository( 'Project\Entity\Project' )->getUserJobs( $item->getYear(), $item->getUser()->getUserId() )
                );
            }
        }

        //echo '<pre>';print_r( $targets );exit;

        $this->getView()
            ->setVariable( 'targets', $targets );

        return $this->getView();
    }

    public function addAction()
    {

        $this->setCaption( 'Target - Add Target' );

        $em   = $this->getEntityManager();
        $form = new \Sales\Form\TargetForm( $em );
        $form->setAttribute( 'action', '/targets/add' )
            ->setAttribute( 'class', 'form-horizontal' );

        $request = $this->getRequest();

        $data = '';

        if ( $request->isPost() )
        {

            $form   = new \Sales\Form\TargetForm( $this->getEntityManager() );
            $target = new \Sales\Entity\Target();
            $form->setInputFilter( $target->getInputFilter() );
            $form->setData( $request->getPost() );
            $post = $request->getPost();
            if ( $form->isValid() )
            {
                $user = $this->getEntityManager()->find( 'Application\Entity\User', $post['user'] );
                $target->setUser( $user );
                $target->exchangeArray( $form->getData() );
                $this->getEntityManager()->persist( $target );
                $this->getEntityManager()->flush();

                // Redirect to list of albums
                $this->flashMessenger()->addSuccessMessage( 'Target has been added successfully!' );

                return $this->redirect()->toRoute( 'targets' );
            }
            else
            {

                $data = array( 'err' => true, 'info' => $form->getMessages() );
            }
        }

        $this->getView()
            ->setVariable( 'form', $form )
            ->setVariable( 'data', $data );

        return $this->getView();
    }

}

