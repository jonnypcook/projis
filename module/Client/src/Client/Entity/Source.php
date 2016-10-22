<?php
namespace Client\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Source")
 */
class Source
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;


    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="SourceType")
     * @ORM\JoinColumn(name="source_type_id", referencedColumnName="source_type_id", nullable=false)
     */
    private $sourceType; 
    
    /**
     * @var integer
     *
     * @ORM\Column(name="source_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sourceId;

	
    public function __construct()
	{
        $this->sourceType = new ArrayCollection();
	}

    public function getName() {
        return $this->name;
    }

    public function getSourceId() {
        return $this->sourceId;
    }
    
    public function getSourceType() {
        return $this->sourceType;
    }

    
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setSourceType($sourceType) {
        $this->sourceType = $sourceType;
        return $this;
    }

    public function setSourceId($sourceId) {
        $this->sourceId = $sourceId;
        return $this;
    }




}
