<?php
namespace Project\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class DocumentEmailForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);


        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'emailRecipient', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'The recipient of the email',
                'data-original-title' => 'Recipient',
                'data-trigger' => 'hover',
                'class' => 'span12  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'emailSubject', // 'usr_name',
            'attributes' => array(
                'type'  => 'text',
                'data-content' => 'The subject of the email',
                'data-original-title' => 'Subject',
                'data-trigger' => 'hover',
                'class' => 'span12  popovers',
            ),
            'options' => array(
            ),
        ));

        $this->add(array(
            'name' => 'emailMessage', // 'usr_name',
            'attributes' => array(
                'type'  => 'textarea',
                'data-content' => 'The message body of the email',
                'data-original-title' => 'Message',
                'data-trigger' => 'hover',
                'class' => 'span12  popovers',
            ),
            'options' => array(
            ),
        ));

    }
    
    protected $objectManager;
    
    public function setObjectManager(\Doctrine\Common\Persistence\ObjectManager $objectManager)
    {
    	$this->objectManager = $objectManager;
    }
    
    public function getObjectManager()
    {
    	return $this->objectManager;
    }
}