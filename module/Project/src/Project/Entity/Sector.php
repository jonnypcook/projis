<?php
namespace Project\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Project_Sector")
 */
class Sector
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="domestic", type="boolean", nullable=false)
     */
    private $domestic;

    /**
     * @var integer
     *
     * @ORM\Column(name="project_sector_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sectorId;

	
    public function __construct()
	{
        $this->setDomestic(false);
	}

    public function getName() {
        return $this->name;
    }

    public function getDomestic() {
        return $this->domestic;
    }

    public function getSectorId() {
        return $this->sectorId;
    }

    
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setDomestic($domestic) {
        $this->domestic = $domestic;
        return $this;
    }


}
