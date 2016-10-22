<?php
namespace Product\Entity;
use Doctrine\ORM\Mapping as ORM;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Product_Type")
 */
class Type
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
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="service", type="boolean", nullable=false)
     */
    private $service;
    

        /**
     * @var integer
     *
     * @ORM\Column(name="product_type_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $typeId;

	
    public function __construct()
	{
        $this->setService(false);
	}

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getTypeId() {
        return $this->typeId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getService() {
        return $this->service;
    }

    public function setService($service) {
        $this->service = $service;
        return $this;
    }
    
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }


}
