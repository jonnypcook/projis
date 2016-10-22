<?php
namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Activity_Type")
 * @ORM\Entity(repositoryClass="Application\Repository\ActivityType")
 */
class ActivityType
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
     * @ORM\Column(name="mins", type="integer", nullable=false)
     */
    private $mins;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="compatibility", type="integer", nullable=false)
     */
    private $compatibility;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="activity_type_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $activityTypeId;

	
    public function __construct()
	{
        $this->setMins(0);
        $this->setCompatibility(0);
	}
    
    public function getCompatibility() {
        return $this->compatibility;
    }

    public function setCompatibility($compatibility) {
        $this->compatibility = $compatibility;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getMins() {
        return $this->mins;
    }

    public function getActivityTypeId() {
        return $this->activityTypeId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setMins($mins) {
        $this->mins = $mins;
        return $this;
    }

    public function setActivityTypeId($activityTypeId) {
        $this->activityTypeId = $activityTypeId;
        return $this;
    }



}
