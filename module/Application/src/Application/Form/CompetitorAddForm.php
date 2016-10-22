<?php
namespace Application\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class CompetitorAddForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, array $config=array())
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Application\Entity\Competitor'));

        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'name', // 'usr_name',
            'type'  => 'text',
            'attributes' => array(
                'data-content' => 'This is the unique name by which this competitor will be referenced',
                'data-original-title' => 'Competitor Name',
                'data-trigger' => 'hover',
                'data-placement' => 'top',
                'class' => 'span10  popovers',
            ),
            'options' => array(
            ),
        ));
        
        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Url',
            'attributes' => array(
                'data-content' => 'Competitor website url',
                'data-original-title' => 'Competitor Website',
                'data-trigger' => 'hover',
                'data-placement' => 'top',
                'class' => 'span10  popovers',
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