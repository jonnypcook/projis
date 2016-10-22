<?php
namespace Sales\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class TargetForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct( \Doctrine\ORM\EntityManager $em )
    {
        $name = preg_replace( '/^[\s\S]*[\\\]([a-z0-9_]+)$/i', '$1', __CLASS__ );
        // we want to ignore the name passed
        parent::__construct( $name );

        $this->setObjectManager( $em );

        $this->setAttribute( 'method', 'post' );

        $this->add( array(
            'name'       => 'salesMonth1',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth1',
                'data-value' => 1,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth1',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth1',
                'data-value' => 1,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth2',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth2',
                'data-value' => 2,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth2',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth2',
                'data-value' => 2,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth3',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth3',
                'data-value' => 3,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth3',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth3',
                'data-value' => 3,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth4',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth4',
                'data-value' => 4,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth4',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth4',
                'data-value' => 4,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth5',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth5',
                'data-value' => 5,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth5',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id'  => 'marginMonth5',
                'data-value' => 5,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth6',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth6',
                'data-value' => 6,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth6',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth6',
                'data-value' => 6,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth7',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth7',
                'data-value' => 7,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth7',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth7',
                'data-value' => 7,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth8',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth8',
                'data-value' => 8,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth8',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth8',
                'data-value' => 8,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth9',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth9',
                'data-value' => 9,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth9',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth9',
                'data-value' => 9,
                'step' => 'any'

            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth10',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth10',
                'data-value' => 10,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth10',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth10',
                'data-value' => 10,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth11',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth11',
                'data-value' => 11,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth11',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth11',
                'data-value' => 11,
                'step' => 'any'
            ),
        ) );

        $this->add( array(
            'name'       => 'salesMonth12',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 sales',
                'id' => 'salesMonth12',
                'data-value' => 12,
                'step' => 'any',
                'tabindex' => -1
            ),
        ) );

        $this->add( array(
            'name'       => 'marginMonth12',
            'attributes' => array(
                'type' => 'number',
                'min'  => 0,
                'class' => 'span12 margin',
                'id' => 'marginMonth12',
                'data-value' => 12,
                'step' => 'any'
            ),
        ) );

        /*
        $this->add( array(
            'type'    => 'Zend\Form\Element\Select',
            'name'    => 'year',
            'options' => array(
                'label'         => 'Select a year',
            )
        ) );
*/

        $year = new Element\Select( 'year' );
        $year->setValueOptions( array(
            date( 'Y' )     => date( 'Y' ),
            date( 'Y' ) + 1 => date( 'Y' ) + 1,
        ) );

        $this->add( $year );


        $this->add( array(
            'type'       => 'DoctrineModule\Form\Element\ObjectSelect',
            'name'       => 'user',
            'attributes' => array(
                'data-original-title' => 'User or Owner',
                'data-trigger'        => 'hover',
                'class'               => 'chzn-select tooltips span12',
            ),
            'options'    => array(
                'empty_option'    => 'Please Select',
                'object_manager'  => $this->getObjectManager(),
                'target_class'    => 'Application\Entity\User',
                'label_generator' => function ( $targetEntity )
                {
                    return $targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                },
                'is_method'       => true,
                'find_method'     => array(
                    'name'   => 'findBy',
                    'params' => array(
                        'criteria' => array(),
                        'orderBy'  => array( 'forename' => 'ASC' )
                    )
                )

            ),
        ) );

        $submit = new Element\Submit('my-submit');
        $submit->setValue('Submit Form');

        $this->add($submit);
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