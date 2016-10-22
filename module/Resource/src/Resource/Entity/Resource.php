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
 * @ORM\Table(name="Resource")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Resource\Repository\Resource")
 */
class Resource
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", nullable=true)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", nullable=true)
     */
    private $unit;

    /**
     * @var float
     *
     * @ORM\Column(name="cost", type="decimal", scale=2, nullable=false)
     */
    private $cost;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Resource\Entity\CostCode")
     * @ORM\JoinColumn(name="cost_code_id", referencedColumnName="cost_code_id", nullable=false)
     */
    private $costCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="resource_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $resourceId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
	}
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getUnit() {
        return $this->unit;
    }

    public function setUnit($unit) {
        $this->unit = $unit;
        return $this;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function getCost() {
        return $this->cost;
    }

    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    public function getCostCode() {
        return $this->costCode;
    }

    public function setCostCode($costCode) {
        $this->costCode = $costCode;
        return $this;
    }

    public function setResourceId($resourceId) {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function getResourceId() {
        return $this->resourceId;
    }

    public function exchangeArray( $data = Array() )
    {
        $this->cost = $data['cost'];
        $this->name = $data['name'];
        $this->unit = $data['unit'];
    }
}


