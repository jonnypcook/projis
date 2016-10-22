<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Contact\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User,    Application\Entity\Address,    Application\Entity\Projects;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;


class ContactController extends \Application\Controller\AuthController
{
    
    public function indexAction()
    {
        $this->setCaption('Contact Management');

        return $this->getView();        
    }
    
    public function listAction() {
        try {
            $data = array();
            if (!$this->request->isXmlHttpRequest()) {
                //throw new \Exception('illegal request type');
            }
            
            $mini = ($this->params()->fromQuery('mini',false)==1);
            
            $em = $this->getEntityManager();
            $length = $this->params()->fromQuery('iDisplayLength', 10);
            $start = $this->params()->fromQuery('iDisplayStart', 1);
            $keyword = $this->params()->fromQuery('sSearch','');
            $params = array(
                'keyword'=>trim($keyword),
                'orderBy'=>array()
            );
            
            if ($mini) {
                $orderBy = array(
                    0=>'forename',
                    1=>'company',
                );
            } else {
                $orderBy = array(
                    0=>'title',
                    1=>'forename',
                    2=>'surname',
                    3=>'position',
                    4=>'telephone',
                    5=>'email',
                );
            }
            for ( $i=0 ; $i<intval($this->params()->fromQuery('iSortingCols',0)) ; $i++ )
            {
                $j = $this->params()->fromQuery('iSortCol_'.$i);
                if ( $this->params()->fromQuery('bSortable_'.$j, false) == "true" )
                {
                    $dir = $this->params()->fromQuery('sSortDir_'.$i,'ASC');
                    if (isset($orderBy[$j])) {
                        $params['orderBy'][$orderBy[$j]]=$dir;
                    }
                }/**/
            }


            $paginator = $em->getRepository('Contact\Entity\Contact')->findPaginateByCompanyId($this->getUser()->getCompany()->getCompanyId(), $length, $start, $params);

            $data = array(
                "sEcho" => intval($this->params()->fromQuery('sEcho', false)),
                "iTotalDisplayRecords" => $paginator->getTotalItemCount(),
                "iTotalRecords" => $paginator->getcurrentItemCount(),
                "aaData" => array()
            );/**/


            if ($mini) {
                foreach ($paginator as $page) {
                    //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
                    $data['aaData'][] = array (
                        '<a href="javascript:" '
                        . 'data-tel1="'.str_replace(' ', '', $page->getTelephone1()).'" '
                        . 'data-tel2="'.str_replace(' ', '', $page->getTelephone2()).'" '
                        . 'data-email="'.$page->getEmail().'" '
                        . 'data-addr="'.($page->getAddress()?$page->getAddress()->assemble():'').'" '
                        . 'data-name="'.$page->getName().'" '
                        . 'data-company="'.$page->getClient()->getName().'" '
                        . 'class="contact-info">'.$page->getName().'</a>',
                        $page->getClient()->getName(),
                        $page->getEmail(),
                    );
                } 
            } else {
                foreach ($paginator as $page) {
                    //$url = $this->url()->fromRoute('client',array('id'=>$page->getclientId()));
                    $notes = $page->getNotes();
                    if (!empty($notes)) {
                        $notes = json_decode($page->getNotes(), true);
                        if (empty($notes)) {
                            $notes='';
                        } else {
                            $notes = '<ul><li>'.implode('</li><li>', $notes).'</li></ul>';
                        }
                    }
                    $data['aaData'][] = array (
                        (!empty($page->getTitle())?(($page->getTitle()->getTitleId()==12)?' ':$page->getTitle()->getName()):' ').
                            '<span '
                        . 'data-tel1="'.str_replace(' ', '', $page->getTelephone1()).'" '
                        . 'data-tel2="'.str_replace(' ', '', $page->getTelephone2()).'" '
                        . 'data-email="'.$page->getEmail().'" '
                        . 'data-addr="'.($page->getAddress()?$page->getAddress()->assemble():'').'" '
                        . 'data-name="'.$page->getName().'" '
                        . 'data-company="'.$page->getClient()->getName().'" '
                        . 'data-buyingtype="'.(empty($page->getbuyingType())?'':$page->getbuyingType()->getName()).'" '
                        . 'data-influence="'.(empty($page->getInfluence())?'':$page->getInfluence()->getName()).'" '
                        . 'data-mode="'.(empty($page->getMode())?'':$page->getMode()->getName()).'" '
                        . 'data-keywin="'.$page->getkeywinresult().'" '
                        . 'data-additional="'.$notes.'" '
                        . 'data-clientId="'.$page->getClient()->getClientId().'" '
                        . 'class="contact-info"></span>',
                        $page->getForename(),
                        $page->getSurname(),
                        $page->getClient()->getName(),
                        $page->getTelephone1(),
                        $page->getEmail(),
                    );
                } 
            }

        } catch (\Exception $ex) {
            $data = array('error'=>true, 'info'=>$ex->getMessage());
        }
        
        return new JsonModel($data);/**/
    }
    
    
    function getAddressAction() {
        try {
            if (!$this->request->isXmlHttpRequest()) {
                throw new \Exception('illegal request type');
            }
            
            $addressId = $this->params()->fromPost('addressId', false);
            if (empty($addressId)) {
                throw new \Exception('No address id found');
            }
            
            $address = $this->getEntityManager()->find('Contact\Entity\Address', $addressId);
            
            if (!($address instanceof \Contact\Entity\Address)) {
                throw new \Exception('Address could not be found');
            }
            
            
            $data = array('err'=>false, 'address'=>array(
                'addressId'=>$addressId,
                'postcode'=>$address->getPostCode(),
                'line1'=>empty($address->getLine1())?'':$address->getLine1(),
                'line2'=>empty($address->getLine2())?'':$address->getLine2(),
                'line3'=>empty($address->getLine3())?'':$address->getLine3(),
                'line4'=>empty($address->getLine4())?'':$address->getLine4(),
                'line5'=>empty($address->getLine5())?'':$address->getLine5(),
                'label'=>empty($address->getLabel())?'':$address->getLabel(),
                'countryId'=>$address->getCountry()->getCountryId(),
            ));
            
        } catch (\Exception $ex) {
            $data = array('error'=>true, 'info'=>$ex->getMessage());
        }
        
        return new JsonModel($data);/**/
    }
    
    
}
