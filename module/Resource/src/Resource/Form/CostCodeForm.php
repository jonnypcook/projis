<?php
namespace Resource\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class CostCodeForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct( \Doctrine\ORM\EntityManager $em )
    {
        $name = preg_replace( '/^[\s\S]*[\\\]([a-z0-9_]+)$/i', '$1', __CLASS__ );
        // we want to ignore the name passed
        parent::__construct( $name );

        $this->setObjectManager( $em );

        $this->setAttribute( 'method', 'post' );

        $this->add( array(
            'type'       => 'text',
            'name'       => 'name',
            'options'    => array(
                'label' => 'Name:'
            ),
            'attributes' => array(
                'id'    => 'name',
                'class' => 'span12'
            )
        ) );

        $submit = new Element\Submit( 'submit' );
        $submit->setValue( 'Submit Form' );

        $this->add( $submit );
    }

    protected $objectManager;

    public function setObjectManager( \Doctrine\Common\Persistence\ObjectManager $objectManager )
    {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }
}