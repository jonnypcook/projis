<?php
namespace Client\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Source_Type")
 */
class SourceType
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
     * @ORM\Column(name="source_type_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sourceTypeId;

	
    public function __construct()
	{
	}

    public function getName() {
        return $this->name;
    }

    public function getSourceTypeId() {
        return $this->sourceTypeId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setSourceTypeId($sourceTypeId) {
        $this->sourceTypeId = $sourceTypeId;
        return $this;
    }


}
