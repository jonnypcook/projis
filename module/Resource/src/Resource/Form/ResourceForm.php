<?php
namespace Resource\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ResourceForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct( \Doctrine\ORM\EntityManager $em )
    {
        $name = preg_replace( '/^[\s\S]*[\\\]([a-z0-9_]+)$/i', '$1', __CLASS__ );
        // we want to ignore the name passed
        parent::__construct( $name );

        $this->setObjectManager( $em );

        $this->setAttribute( 'method', 'post' );
        //$this->setAttribute( 'id', $name );

        $this->add( array(
            'type'       => 'DoctrineModule\Form\Element\ObjectSelect',
            'name'       => 'costCode',
            'attributes' => array(
                'data-original-title' => 'Cost Code',
                'data-trigger'        => 'hover',
                'class'               => 'tooltips span12'
            ),
            'options'    => array(
                'empty_option'    => 'Please Select',
                'object_manager'  => $this->getObjectManager(),
                'target_class'    => 'Resource\Entity\CostCode',
                'is_method'       => true,
                'label'           => 'Cost Code:',
                'label_generator' => function ( $costcode )
                {
                    return $costcode->getName();
                },
                'find_method'     => array(
                    'name'   => 'findCostCodes',
                    'params' => array(
                        'criteria' => array(),
                    )
                )
            ),
        ) );

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

        $this->add( array(
            'type'       => 'text',
            'name'       => 'unit',
            'options'    => array(
                'label' => 'Unit:'
            ),
            'attributes' => array(
                'id'    => 'unit',
                'class' => 'span12'
            )
        ) );

        $this->add( array(
            'type'       => 'text',
            'name'       => 'cost',
            'options'    => array(
                'label' => 'Cost:'
            ),
            'attributes' => array(
                'id'    => 'cost',
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