<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

use Application\Controller\AuthController;
use User\Form\ChangePasswordForm;
use User\Form\ChangePasswordFilter;

class UserController extends AuthController
{
    
    public function onDispatch(MvcEvent $e) {
        return parent::onDispatch($e);
    }
    
    /**
     * main Dashboard action
     * @return \Zend\View\Model\ViewModel
     */
    public function profileAction()
    {
        $this->setCaption('User Profile');

        $this->getView()
                ->setVariable('user', $this->getUser())
        ;/**/
        
        return $this->getView();
    }
    
    
    public function passwordAction()
    {
        $this->setCaption('Change Password');
        $form = new ChangePasswordForm();
        
        $form->setAttribute('action', '/user/passwordSave/');

        $this->getView()
                
                ->setVariable('form', $form)
                ;
        /*$this->getView()
                ->setVariable('info', $info)
                ->setVariable('activities', $activities)
                ->setVariable('user', $this->getUser())
                ->setVariable('formActivity', $formActivity);/**/
        
        return $this->getView();
    }
    
    public function passwordSaveAction()
    {
        try {
            if (!($this->getRequest()->isXmlHttpRequest())) {
                throw new \Exception('illegal request');
            }
            
            $form = new ChangePasswordForm();
            $form->setInputFilter(new ChangePasswordFilter());
            
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                // check password
                if ($this->getUser()->getPassword() != md5('aFGQ475SDsdfsaf2342' . $postData['password'] . $this->getUser()->getPasswordSalt())) {
                    $data = array('err'=>true, 'info'=>array('password'=>array('You have entered an incorrect password')));
                } else {
                    $this->getUser()->setPassword(md5('aFGQ475SDsdfsaf2342' . $postData['newPassword'] . $this->getUser()->getPasswordSalt()));
                    $dt = new \DateTime();
                    $dt->setTimestamp(time()+(60*60*24*365));
                    
                    $this->getUser()->setPasswordExpiryDate($dt);
                    $this->getEntityManager()->persist($this->getUser());
                    $this->getEntityManager()->flush();
                    $data = array('err'=>false);
                }
            } else {
                $data = array('err'=>true, 'info'=>$form->getMessages());
            }
            
            
            //echo '<pre>', print_r($data, true), '</pre>';
            //echo '<pre>', print_r($evts, true), '</pre>';
        
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        return new JsonModel($data);/**/
    }
    
    
    public function grevokeAction() {
        try {
            if (!$this->request->isXmlHttpRequest()) {
                throw new \Exception('illegal request type');
            }
            
            $this->revokeGoogle();
            
            $data = array('err'=>false);
        } catch (\Exception $ex) {
            $data = array('err'=>true, 'info'=>array('ex'=>$ex->getMessage()));
        }
        
        return new JsonModel(empty($data)?array('err'=>true):$data);/**/
    }
    
    
}
