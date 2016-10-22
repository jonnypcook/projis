<?php
namespace Resource\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/**
 * @ORM\Table(name="Resource_Activity")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Resource\Repository\ResourceActivity")
 */
class ResourceActivity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="resource_activity_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $resourceActivityId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Resource\Entity\CostCode")
     * @ORM\JoinColumn(name="cost_code_id", referencedColumnName="cost_code_id", nullable=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $costCode;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", nullable=true)
     */
    private $reference;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Resource\Entity\Resource")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="resource_id", nullable=true)
     */
    private $resource;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Resource\Entity\Status")
     * @ORM\JoinColumn(name="resource_status_id", referencedColumnName="resource_status_id", nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="string", nullable=true)
     */
    private $details;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="decimal", scale=5, nullable=true)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="decimal", scale=5, nullable=true)
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(name="reference_type", nullable=true)
     */
    private $referenceType;

    /**
     * @var integer
     *
     * @ORM\Column(name="project_id", nullable=true)
     */
    private $project;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;


    public function __construct()
    {
        $this->setCreated( new \DateTime() );
        $this->setDate( new \DateTime() );
        $this->setStartDate( new \DateTime() );
        $this->setEndDate( new \DateTime() );
        $this->project  = new ArrayCollection();
        $this->costCode = new ArrayCollection();
        $this->resource = new ArrayCollection();
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference( $reference )
    {
        $this->reference = $reference;

        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource( $resource )
    {
        $this->resource = $resource;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated( \DateTime $created )
    {
        $this->created = $created;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate( \DateTime $date )
    {
        $this->date = $date;

        return $this;
    }

    public function getCostCode()
    {
        return $this->costCode;
    }

    public function setCostCode( $costCode )
    {
        $this->costCode = $costCode;

        return $this;
    }

    public function setResourceActivityId( $resourceActivityId )
    {
        $this->resourceActivityId = $resourceActivityId;

        return $this;
    }

    public function getResourceActivityId()
    {
        return $this->resourceActivityId;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity( $quantity )
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function setRate( $rate )
    {
        $this->rate = $rate;

        return $this;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setProject( $project )
    {
        $this->project = $project;

        return $this;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails( $details )
    {
        $this->details = $details;

        return $this;
    }

    public function setReferenceType( $referenceType )
    {
        return $this->referenceType = $referenceType;

        return $this;
    }

    public function getReferenceType()
    {
        return $this->referenceType;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate( \DateTime $startDate )
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate( \DateTime $endDate )
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function exchangeArray( $data = array() )
    {
        $this->details   = $data['details'];
        $this->quantity  = $data['quantity'];
        $this->rate      = $data['rate'];
        $this->reference = $data['reference'];
        $this->project   = $data['project'];
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars( $this );
    }

    protected $inputFilter;

    /**
     * set the input filter- only in forstructural and inheritance purposes
     *
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     *
     * @throws \Exception
     */
    public function setInputFilter( InputFilterInterface $inputFilter )
    {
        throw new \Exception( "Not used" );
    }


    /**
     *
     * @return Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        if ( !$this->inputFilter )
        {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            //example in google under search: "zf2 doctrine Album example"
            /*
            $inputFilter->add( $factory->createInput( array(
                'name'       => 'date', // 'usr_name'
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );
            */
            $inputFilter->add( $factory->createInput( array(
                'name'       => 'costCode',
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );

            $inputFilter->add( $factory->createInput( array(
                'name'       => 'resource',
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );
            /*
            $inputFilter->add( $factory->createInput( array(
                'name'       => 'details',
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );
            */

            $inputFilter->add( $factory->createInput( array(
                'name'       => 'quantity',
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );

            $inputFilter->add( $factory->createInput( array(
                'name'       => 'rate',
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );

            $inputFilter->add( $factory->createInput( array(
                'name'       => 'project',
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );

            $this->inputFilter = $inputFilter;

        }

        return $this->inputFilter;
    }
}


