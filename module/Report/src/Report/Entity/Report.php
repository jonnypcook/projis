<?php
namespace Report\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/** 
 * @ORM\Table(name="Report")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Report\Repository\Report")
 */
class Report 
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
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
     * @ORM\Column(name="permission", type="string", length=128, nullable=true)
     */
    private $permission;
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Report\Entity\Group")
     * @ORM\JoinColumn(name="report_group_id", referencedColumnName="report_group_id", nullable=true)
     */
    private $group; 
    

    /**
     * @var integer
     *
     * @ORM\Column(name="report_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $reportId;
    
  
    public function __construct()
	{
        $this->group = new ArrayCollection();
	}
    
    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPermission() {
        return $this->permission;
    }

    public function getGroup() {
        return $this->group;
    }

    public function getReportId() {
        return $this->reportId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setPermission($permission) {
        $this->permission = $permission;
        return $this;
    }

    public function setGroup($group) {
        $this->group = $group;
        return $this;
    }

    public function setReportId($reportId) {
        $this->reportId = $reportId;
        return $this;
    }


    
}


