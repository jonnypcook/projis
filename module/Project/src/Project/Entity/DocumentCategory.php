<?php
namespace Project\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="DocumentCategory")
 */
class DocumentCategory
{
    /**
     * @var string
     *
     * @ORM\Column(name="location", type="text", nullable=false)
     */
    private $location;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
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
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    private $config;

    /**
     * @var string
     *
     * @ORM\Column(name="partial", type="string", length=64, nullable=false)
     */
    private $partial;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="compatibility", type="integer", nullable=false)
     */
    private $compatibility;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="grouping", type="integer", nullable=true)
     */
    private $grouping;
    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;


    /**
     * @var integer
     *
     * @ORM\Column(name="document_category_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $documentCategoryId;

	
    public function __construct()
	{
        $this->setCompatibility(0);
        $this->setActive(true);
	}
    
    public function getGrouping() {
        return $this->grouping;
    }

    public function setGrouping($grouping) {
        $this->grouping = $grouping;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getPartial() {
        return $this->partial;
    }

    public function getCompatibility() {
        return $this->compatibility;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

    public function setPartial($partial) {
        $this->partial = $partial;
        return $this;
    }

    public function setCompatibility($compatibility) {
        $this->compatibility = $compatibility;
        return $this;
    }

    public function getActive() {
        return $this->active;
    }

    public function setActive($active) {
        $this->active = $active;
        return $this;
    }

    public function getDocumentCategoryId() {
        return $this->documentCategoryId;
    }

    public function setDocumentCategoryId($documentCategoryId) {
        $this->documentCategoryId = $documentCategoryId;
        return $this;
    }

    public function getLocation() {
        return $this->location;
    }

    public function setLocation($location) {
        $this->location = $location;
        return $this;
    }



    
}


