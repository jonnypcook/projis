<?php
namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Audit_Type")
 */
class AuditType
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
     * @ORM\Column(name="icon", type="string", length=32, nullable=true)
     */
    private $icon;
    
    /**
     * @var string
     *
     * @ORM\Column(name="box", type="string", length=32, nullable=true)
     */
    private $box;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="auto", type="boolean", nullable=false)
     */
    private $auto;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="audit_type_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $auditTypeId;

	
    public function __construct()
	{
        $this->setAuto(false);
	}
    
    public function getName() {
        return $this->name;
    }

    public function getAuditTypeId() {
        return $this->auditTypeId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setAuditTypeId($auditTypeId) {
        $this->auditTypeId = $auditTypeId;
        return $this;
    }

    public function getAuto() {
        return $this->auto;
    }

    public function setAuto($auto) {
        $this->auto = $auto;
        return $this;
    }

    public function getIcon() {
        return $this->icon;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

    public function getBox() {
        return $this->box;
    }

    public function setBox($box) {
        $this->box = $box;
        return $this;
    }






}
