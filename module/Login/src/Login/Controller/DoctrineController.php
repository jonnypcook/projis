<?php
namespace Login\Controller;

// Authentication with Remember Me
// http://samsonasik.wordpress.com/2012/10/23/zend-framework-2-create-login-authentication-using-authenticationservice-with-rememberme/

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Json\Json;

use Application\Entity\User; // only for the filters

use Login\Form\LoginForm;       // <-- Add this import
use Login\Form\LoginFilter;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class DoctrineController extends AbstractActionController
{
    public function indexAction()
    {
        $em = $this->getEntityManager();
		$auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');

        $boxmode = $this->params()->fromQuery('box', false);
        $boxmode = !empty($boxmode);
        
		if ($auth->hasIdentity()) {
			// Identity exists; get it
            return $this->redirect()->toRoute('home');
        }
        
        $form = new LoginForm();
		$messages = null;

		$request = $this->getRequest();
        if ($request->isPost()) {
            //- $authFormFilters = new User(); // we use the Entity for the filters
			// TODO fix the filters
            //- $form->setInputFilter($authFormFilters->getInputFilter());
			// Filters have been fixed
			$form->setInputFilter(new LoginFilter($this->getServiceLocator()));
            $form->setData($request->getPost());
			// echo "<h1>I am here1</h1>";
            if ($form->isValid()) {
				$data = $form->getData();			

                // $data = $this->getRequest()->getPost();
				// If you used another name for the authentication service, change it here
				// it simply returns the Doctrine Auth. This is all it does. lets first create the connection to the DB and the Entity
				$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');		
				// Do the same you did for the ordinar Zend AuthService	
				$adapter = $authService->getAdapter();
				$adapter->setIdentityValue($data['username']); //$data['usr_name']
				$adapter->setCredentialValue($data['password']); // $data['usr_password']
				$authResult = $authService->authenticate();

                if ($authResult->isValid()) {
					$user = $authResult->getIdentity();
                    $this->logon($authService, $user, $data['rememberme']);
					
                    if ($boxmode) {
                        return $this->redirect()->toRoute('loginbox');
                    } else {
    					return $this->redirect()->toRoute('home');
                    }
				}
				foreach ($authResult->getMessages() as $message) {
					$messages .= "$message\n"; 
				}	

			} else {
                //print_r($form->getMessages());
                //die('nv');
            }
		}
        
        $this->layout()->setVariable('boxmode', $boxmode);
        
		return new ViewModel(array(
			'error' => 'Your authentication credentials are not valid',
			'form'	=> $form,
			'messages' => $messages,
            'boxmode' => $boxmode,
		));
    }

    public function logoutAction()
	{
		// in the controller
		// $auth = new AuthenticationService();
		$auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');

		// @todo Set up the auth adapter, $authAdapter
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
            $this->AuditPlugin()->audit(2, $identity->getUserId());
		}
		$auth->clearIdentity();
		$sessionManager = new \Zend\Session\SessionManager();
		$sessionManager->forgetMe();

        return $this->redirect()->toRoute('login');

	}

    /**             
	 * @var Doctrine\ORM\EntityManager
	 */                
	protected $em;

	public function getEntityManager()
	{
		if (null === $this->em) {
			$this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
		}
		return $this->em;
	}
    
    
    /**
     * create user session
     * @param \Zend\Authentication\AuthenticationService $authService
     * @param Application\Entity\User $user
     * @param type $persist
     * @return boolean
     */
    public function logon(\Zend\Authentication\AuthenticationService $authService, $user, $persist=true) {
        try {
            $authService->getStorage()->write($user);

            if ($persist) {
                $time = 1410259678; // 44 years //1209600; // 14 days 1209600/3600 = 336 hours => 336/24 = 14 days
                $sessionManager = new \Zend\Session\SessionManager();
                $sessionManager->rememberMe($time);
            }

            $this->AuditPlugin()->audit(1, $user->getUserId());
            
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    
    public function oauth2googleAction() {
        try {
            // grab local config
            $config = $this->getServiceLocator()->get('Config');
            
            $client = new \Google_Client();
            $client->setClientId($config['openAuth2']['google']['clientId']);
            $client->setClientSecret($config['openAuth2']['google']['clientSecret']);
            $client->setAccessType($config['openAuth2']['google']['accessType']);
            $client->setRedirectUri($config['openAuth2']['google']['redirectUri']);
            $client->setScopes($config['openAuth2']['google']['scope']);

            $code = $this->params()->fromQuery('code', false);
            if (empty($code)) {
                $this->redirect()->toUrl($client->createAuthUrl());
                return;
            } 
            
            $client->authenticate($code);
            
            // We got an access token, let's now get the user's details
            $plus = new \Google_Service_Oauth2($client);
            $me = $plus->userinfo_v2_me->get();
            
            // find user
            $user = $this->getEntityManager()->getRepository('Application\Entity\User')->findByEmail($me->getEmail());

            // check user id
            if (empty($user->getGoogle_id()) || ($user->getGoogle_id() != $me->getId())) {
                $user->setGoogle_Id($me->getId());
                //die ('This user has not been admin passed yet - add to task list for administrator');
            }

            // user is fine to proceed add the 
            $user->setToken_access($client->getAccessToken());
            $token = json_decode($client->getAccessToken());
            if (!empty($token->refresh_token)) {
                $user->setToken_refresh($token->refresh_token);
            }

            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();

            $this->logon($this->getServiceLocator()->get('Zend\Authentication\AuthenticationService'), $user);
            return $this->redirect()->toRoute('home');
        } catch (\Exception $e) {
            echo $e->getMessage();
            die('<br /><a href="/oauth2google">restart</a>');
            return $this->redirect()->toRoute('login');
        }
    }
    
    public function successAction() {
        $this->layout()->setVariable('boxmode', true);
        $this->layout()->setVariable('success', true);

        // Create and return a view model for the retrieved article
        return new ViewModel();
    }
    
}

/*
 * League Request = https://accounts.google.com/o/oauth2/token
 * 
 * 
 * 
 */