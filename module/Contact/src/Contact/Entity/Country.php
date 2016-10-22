<?php
namespace Contact\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Country")
 */
class Country 
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="iso4217", type="string", length=3, nullable=false)
     */
    private $iso4217; // currency


    /**
     * @var string
     *
     * @ORM\Column(name="iso31662", type="string", length=2, nullable=false)
     */
    private $iso31662;


    /**
     * @var string
     *
     * @ORM\Column(name="iso31663", type="string", length=3, nullable=false)
     */
    private $iso31663;

    
    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var integer
     *
     * @ORM\Column(name="country_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $countryId;

	
    public function __construct()
	{

    }
    
    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
        return $this;
    }

    
    public function getName() {
        return $this->name;
    }

    public function getIso4217() {
        return $this->iso4217;
    }

    public function getIso31662() {
        return $this->iso31662;
    }

    public function getIso31663() {
        return $this->iso31663;
    }

    public function getCountryId() {
        return $this->countryId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setIso4217($iso4217) {
        $this->iso4217 = $iso4217;
        return $this;
    }

    public function setIso31662($iso31662) {
        $this->iso31662 = $iso31662;
        return $this;
    }

    public function setIso31663($iso31663) {
        $this->iso31663 = $iso31663;
        return $this;
    }

    public function setCountryId($countryId) {
        $this->countryId = $countryId;
        return $this;
    }



}
