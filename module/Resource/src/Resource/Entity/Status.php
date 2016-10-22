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
 * @ORM\Table(name="Resource_Status")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Resource\Repository\Status")
 */
class Status
{

    /**
     * @var integer
     *
     * @ORM\Column(name="resource_status_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $resourceStatusId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", nullable=true)
     */
    private $color;

	
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

    public function setResourceStatusId($id) {
        $this->resourceStatusId = $id;
        return $this;
    }

    public function getResourceStatusId() {
        return $this->resourceStatusId;
    }

    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }
}


