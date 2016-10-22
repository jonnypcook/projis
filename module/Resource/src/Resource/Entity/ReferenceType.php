<?php
namespace Resource\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Table(name="Resource_Reference_Type")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Resource\Repository\ReferenceType")
 */
class ReferenceType
{

    /**
     * @var integer
     *
     * @ORM\Column(name="reference_type_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $referenceTypeId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    public function __construct()
	{
	}
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setReferenceTypeId($id) {
        $this->referenceTypeId = $id;
        return $this;
    }

    public function getReferenceTypeId() {
        return $this->referenceTypeId;
    }
}


