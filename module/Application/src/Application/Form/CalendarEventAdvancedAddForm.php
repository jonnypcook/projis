<?php
namespace Application\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class CalendarEventAdvancedAddForm extends Form  implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, array $config=array())
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'title', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Title of the event',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'class' => 'tooltips span12',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'location', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Location of the event',
                'data-placement' => 'left',
                'data-trigger' => 'hover',
                'class' => 'tooltips span12',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'allday', // 'usr_password',
            'type'  => 'CheckBox',
            'attributes' => array(
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'sendNotifications', // 'usr_password',
            'type'  => 'CheckBox',
            'attributes' => array(
                'value'=>true
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'description', 
            'attributes' => array(
                'type'  => 'textarea',
                'data-original-title' => 'Detailed description of the event',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'class' => 'tooltips  span12',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calStartDt', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Start date of the event',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'data-mask' => '99/99/9999',
                'class' => 'tooltips ',
                'style' => 'width: 100px',
                //'readonly' => 'true',
                'value' => date('d/m/Y'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calStartTm', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Start time of the event',
                'data-trigger' => 'hover',
                'class' => 'tooltips ',
                'data-placement' => 'right',
                'style' => 'width: 100px',
                'data-mask' => '99:99',
                //'readonly' => 'true',
                'value' => date('H:i'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calEndDt', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'End date of the event',
                'data-trigger' => 'hover',
                'data-mask' => '99/99/9999',
                'data-placement' => 'left',
                'class' => 'tooltips ',
                'style' => 'width: 100px',
                //'readonly' => 'true',
                'value' => date('d/m/Y'),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'calEndTm', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'End time of the event',
                'data-trigger' => 'hover',
                'data-placement' => 'right',
                'class' => 'tooltips ',
                'style' => 'width: 100px',
                'data-mask' => '99:99',
                //'readonly' => 'true',
                'value' => date('H:i',time()+(60*30)),
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'note', 
            'attributes' => array(
                'type'  => 'text',
                'class' => ' ',
                //'readonly' => 'true',
                'placeholder'=>'Enter details of the activity'
            ),
            'options' => array(
            ),
        ));
        
       
        /*$this->add(array(     
            'name' => 'users',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',       
            'attributes' =>  array(
                'class' => 'chzn-select span12',
                'multiple' => true,
                'data-placeholder' => "Click box to select invited users"
            ),
            'options' => array(
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Application\Entity\User',
                'order_by'=>'forename',
                'label_generator' => function($targetEntity) {
                    return $targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                },
                'is_method' => true,
                'find_method' => array(
                    'name' => 'findByCompany',
                    'params' => array(
                        'companyId'=>$config['companyId'],
                        'criteria' => array(),
                        'orderBy' => array('forename' => 'ASC')
                    )
                ) 
            ),
        ));  /**/
        
        $this->add(array(     
            'name' => 'users',
            'type' => 'select',       
            'attributes' =>  array(
                'class' => 'chzn-select span12',
                'multiple' => true,
                'data-placeholder' => "Click box to select invited users"
            ),
            'options' => array(
                
            ),
        ));  /**/
                
        $this->add(array(
            'name' => 'usersBespoke', 
            'attributes' => array(
                'type'  => 'text',
                'data-original-title' => 'Invited Non-company Users',
                'data-trigger' => 'hover',
                'data-placement' => 'left',
                'class' => 'tags span12',
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