<?php
namespace Resource\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class ResourceActivityForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct( \Doctrine\ORM\EntityManager $em )
    {
        $name = preg_replace( '/^[\s\S]*[\\\]([a-z0-9_]+)$/i', '$1', __CLASS__ );
        // we want to ignore the name passed
        parent::__construct( $name );

        $this->setObjectManager( $em );


        $this->setAttribute( 'method', 'post' );

        /*
        $this->add( array(
            'name'       => 'projectId',
            'type'       => 'hidden',
            'attributes' => array(
                'value' => $project->getProjectId(),
            ),
        ) );
        */

        $this->add( array(
            'type'       => 'DoctrineModule\Form\Element\ObjectSelect',
            'name'       => 'costCode',
            'attributes' => array(
                'data-original-title' => 'Cost Code',
                'data-trigger'        => 'hover',
                'class'               => 'tooltips span12',
                'tabindex'            => 3,
                'id'                  => 'costCode'
            ),
            'options'    => array(
                'empty_option'    => 'Please Select',
                'object_manager'  => $this->getObjectManager(),
                'target_class'    => 'Resource\Entity\CostCode',
                'is_method'       => true,
                'label_generator' => function ( $costcode )
                {
                    return $costcode->getName();
                },
                'find_method'     => array(
                    'name'   => 'findCostCodes',
                    'params' => array(
                        'criteria' => array()
                    )
                )
            ),
        ) );

        $this->add( array(
            'type'       => 'text',
            'name'       => 'reference',
            'attributes' => array(
                'id'       => 'reference',
                'tabindex' => 5
            )
        ) );

        $this->add( array(
            'type'       => 'DoctrineModule\Form\Element\ObjectSelect',
            'name'       => 'resource',
            'attributes' => array(
                'data-original-title' => 'Resource',
                'data-trigger'        => 'hover',
                'class'               => 'tooltips span12',
                'tabindex'            => 2,
                'id'                  => 'resource'
            ),
            'options'    => array(
                'empty_option'    => 'Please Select',
                'object_manager'  => $this->getObjectManager(),
                'target_class'    => 'Resource\Entity\Resource',
                'is_method'       => true,
                'label_generator' => function ( $resource )
                {
                    return $resource->getName();
                },
                'find_method'     => array(
                    'name'   => 'findResources',
                    'params' => array(
                        'criteria' => array()
                    )
                )
            ),
        ) );

        $this->add( array(
            'type'       => 'text',
            'name'       => 'details',
            'attributes' => array(
                'id'       => 'details',
                'tabindex' => 6
            )
        ) );

        $this->add( array(
            'type'       => 'number',
            'name'       => 'quantity',
            'attributes' => array(
                'id'       => 'quantity',
                'tabindex' => 7,
                'step'     => 'any',

            )
        ) );

        $this->add( array(
            'type'       => 'number',
            'name'       => 'rate',
            'attributes' => array(
                'id'       => 'rate',
                'step'     => 'any',
                'tabindex' => 8,
                'min'      => 0
            )
        ) );

        $this->add( array(
            'type'       => 'number',
            'name'       => 'project',
            'attributes' => array(
                'id'       => 'project',
                'step'     => 'any',
                'tabindex' => 4
            )
        ) );
        /*
        $this->add( array(
            'type'       => 'DoctrineModule\Form\Element\ObjectRadio',
            'name'       => 'status',
            'attributes' => array(
                'data-original-title' => 'Status',
                'data-trigger'        => 'hover',
                'class'               => 'chzn-select tooltips span6',
                'tabindex'            => 5
            ),
            'options'    => array(
                'object_manager'  => $this->getObjectManager(),
                'target_class'    => 'Resource\Entity\Status',
                'is_method'       => true,
                'find_method'     => array(
                    'name'   => 'findBy',
                    'params' => array(
                        'criteria' => array()
                    )
                )
            ),
        ) );
        */
        /*
        $this->add( array(
            'type'       => 'DoctrineModule\Form\Element\ObjectSelect',
            'name'       => 'project',
            'attributes' => array(
                'data-original-title' => 'Project',
                'data-trigger'        => 'hover',
                'class'               => 'chzn-select tooltips span6',
                'id'                  => 'project',
                'tabindex'            => 4
            ),
            'options'    => array(
                'empty_option'    => 'Please Select',
                'object_manager'  => $this->getObjectManager(),
                'target_class'    => 'Project\Entity\Project',
                'label_generator' => function ( $project )
                {
                    return $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName();
                },
                'is_method'       => true,
                'find_method'     => array(
                    'name'   => 'findBy',
                    'params' => array(
                        'criteria' => array()
                    )
                )
            ),
        ) );
        */


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