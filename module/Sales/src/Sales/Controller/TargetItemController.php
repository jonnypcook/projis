<?php

namespace Sales\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Controller\AuthController;

use Zend\Mvc\MvcEvent;

use Sales\Form;

class TargetItemController extends TargetSpecificController
{

    public function indexAction()
    {
        $target = $this->getTarget();

        $this->getView()
            ->setVariable( 'target', $target );

        return $this->getView();
    }


    public function editAction()
    {
        $this->setCaption( 'Target - Edit' );

        $target = $this->getTarget();

        $form = new \Sales\Form\TargetForm( $this->getEntityManager() );
        $form->setAttribute( 'action', '/target-' . $target->getTargetId() . '/edit' )
            ->setAttribute( 'class', 'form-horizontal' );

        $form->bind( $target );

        $request = $this->getRequest();
        $data    = array( 'err' => false, 'info' => '' );
        if ( $request->isPost() )
        {
            $post = $request->getPost();
            $user = $this->getEntityManager()->find( 'Application\Entity\User', $post['user'] );
            $target->setUser( $user );
            $form->setInputFilter( $target->getInputFilter() );
            $form->setData( $request->getPost() );

            if ( $form->isValid() )
            {
                $this->getEntityManager()->flush();

                $this->flashMessenger()->addSuccessMessage( 'Target has been updated successfully!' );

                return $this->redirect()->toRoute( 'targets' );
            }
            else
            {
                $data = array( 'err' => true, 'info' => $form->getMessages() );
            }
        }

        $this->getView()
            ->setVariable( 'id', $target->getTargetId() )
            ->setVariable( 'target', $target )
            ->setVariable( 'form', $form );

        return $this->getView();
    }

    public function deleteAction()
    {
        $target  = $this->getTarget();
        $this->getEntityManager()->remove($target);
        $this->getEntityManager()->flush();
        $this->flashMessenger()->addSuccessMessage( 'Target has been deleted successfully!' );
        return $this->redirect()->toRoute( 'targets' );
    }

    public function copyAction()
    {
        $target = $this->getTarget();

        $new_target = clone $target;

        $this->getEntityManager()->persist($new_target);
        $this->getEntityManager()->flush();

        $id = $new_target->getTargetId();
        $this->flashMessenger()->addSuccessMessage('Target has been copied successfully, please update records and submit');
        return $this->redirect()->toRoute('target', array('action'=> 'edit', 'id' => $id ));

    }

}

