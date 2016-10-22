<?php
namespace Product\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Product_Build")
 */
class Build
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false, unique=true)
     */
    private $name;


    /**
     * @var integer
     *
     * @ORM\Column(name="leadtime", type="integer", nullable=false)
     */
    private $leadtime;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="risk", type="integer", nullable=false)
     */
    private $risk;


    /**
     * @var integer
     *
     * @ORM\Column(name="product_build_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $buildId;

	
    public function __construct()
	{
        $this->risk = 0;
	}

    public function getName() {
        return $this->name;
    }

    public function getRisk() {
        return $this->risk;
    }

    public function getBuildId() {
        return $this->buildId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setRisk($risk) {
        $this->risk = $risk;
        return $this;
    }

    public function setBuildId($buildId) {
        $this->buildId = $buildId;
        return $this;
    }

    public function getLeadtime() {
        return $this->leadtime;
    }

    public function setLeadtime($leadtime) {
        $this->leadtime = $leadtime;
        return $this;
    }


}
