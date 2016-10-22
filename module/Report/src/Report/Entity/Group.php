<?php
namespace Report\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary


/** 
 * @ORM\Table(name="Report_Group")
 * @ORM\Entity 
 */
class Group 
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", length=64, type="string", nullable=false)
     */
    private $name;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="icon", length=64, type="string", nullable=false)
     */
    private $icon;
    

    /**
     * @var string
     *
     * @ORM\Column(name="colour", length=64, type="string", nullable=false)
     */
    private $colour;
    

    /**
     * @var integer
     *
     * @ORM\Column(name="report_group_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $groupId;
    
  
    public function __construct()
	{
	}
    
    public function getColour() {
        return $this->colour;
    }

    public function setColour($colour) {
        $this->colour = $colour;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getIcon() {
        return $this->icon;
    }

    public function getGroupId() {
        return $this->groupId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

    public function setGroupId($groupId) {
        $this->groupId = $groupId;
        return $this;
    }

            
    

}


