<?php
namespace Job\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Table(name="Serial")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Job\Repository\Serial")
 */
class Serial 
{
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="serials")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)
     */
    private $project; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Space\Entity\Space", inversedBy="serials")
     * @ORM\JoinColumn(name="space_id", referencedColumnName="space_id", nullable=true)
     */
    private $space; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Space\Entity\System")
     * @ORM\JoinColumn(name="system_id", referencedColumnName="system_id", nullable=true)
     */
    private $system; 
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="serial_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $serialId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        
        //$this->system = new ArrayCollection();
        //$this->project = new ArrayCollection();
        //$this->space = new ArrayCollection();
	}
    
    public function getSystem() {
        return $this->system;
    }

    public function setSystem($system) {
        $this->system = $system;
        return $this;
    }

    public function getSpace() {
        return $this->space;
    }

    public function setSpace($space) {
        $this->space = $space;
        return $this;
    }

            
    public function getCreated() {
        return $this->created;
    }

    public function getProject() {
        return $this->project;
    }

    public function getSerialId() {
        return $this->serialId;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function setSerialId($serialId) {
        $this->serialId = $serialId;
        return $this;
    }


    

}


