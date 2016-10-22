<?php
namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Table(name="Department")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="User\Repository\Department")
 */
class Department
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="department_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $departmentId;
    
    
    public function __construct()
	{
	}

    public function getDepartmentId() {
        return $this->departmentId;
    }

    public function setDepartmentId($departmentId) {
        $this->departmentId = $departmentId;

        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;

        return $this;
    }
}


