<?php
namespace Project\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Project_Status")
 */
class Status
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;
    
    /**
     * @var float
     *
     * @ORM\Column(name="weighting", type="float", precision=2, nullable=false)
     */
    private $weighting;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="job", type="boolean", nullable=false)
     */
    private $job;

    /**
     * @var boolean
     *
     * @ORM\Column(name="halt", type="boolean", nullable=false)
     */
    private $halt;


    /**
     * @var integer
     *
     * @ORM\Column(name="project_status_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $statusId;

	
    public function __construct()
	{
        
	}

    public function getName() {
        return $this->name;
    }

    public function getWeighting() {
        return $this->weighting;
    }

    public function getJob() {
        return $this->job;
    }

    public function getHalt() {
        return $this->halt;
    }

    public function getStatusId() {
        return $this->statusId;
    }

        
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setWeighting($weighting) {
        $this->weighting = $weighting;
        return $this;
    }

    public function setJob($job) {
        $this->job = $job;
        return $this;
    }

    public function setHalt($halt) {
        $this->halt = $halt;
        return $this;
    }

    public function setStatusId($statusId) {
        $this->statusId = $statusId;
        return $this;
    }


}
