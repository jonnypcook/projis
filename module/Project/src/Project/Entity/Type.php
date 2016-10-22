<?php
namespace Project\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Project_Type")
 */
class Type
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="processor", type="string", length=100, nullable=false)
     */
    private $processor;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="project_type_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $typeId;

	
    public function __construct()
	{
        $this->setDomestic(false);
	}

    public function getName() {
        return $this->name;
    }

    public function getProcessor() {
        return $this->processor;
    }

    public function getTypeId() {
        return $this->typeId;
    }

        
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setProcessor($processor) {
        $this->processor = $processor;
        return $this;
    }


}
