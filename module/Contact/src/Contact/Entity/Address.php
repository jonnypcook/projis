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
 * @ORM\Table(name="Address")
 * @ORM\Entity(repositoryClass="Contact\Repository\Address")
 */
class Address implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=256, nullable=true)
     */
    private $label;


    /**
     * @var string
     *
     * @ORM\Column(name="line1", type="string", length=256)
     */
    private $line1;


    /**
     * @var string
     *
     * @ORM\Column(name="line2", type="string", length=256, nullable=true)
     */
    private $line2;


    /**
     * @var string
     *
     * @ORM\Column(name="line3", type="string", length=256, nullable=true)
     */
    private $line3;


    /**
     * @var string
     *
     * @ORM\Column(name="line4", type="string", length=256, nullable=true)
     */
    private $line4;


    /**
     * @var string
     *
     * @ORM\Column(name="line5", type="string", length=256, nullable=true)
     */
    private $line5;


    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=8)
     */
    private $postcode;


    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", nullable=true)
     */
    private $lat;
    

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", nullable=true)
     */
    private $lng;
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="country_id", nullable=false)
     */
    private $country; 
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Client\Entity\Client")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="client_id", nullable=true)
     */
    private $client; 
    
    /**
     * @var integer
     *
     * @ORM\Column(name="address_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $addressId;

	
    public function __construct()
	{
        $this->country= new ArrayCollection();
        $this->client= new ArrayCollection();
		$this->setCreated(new \DateTime());
	}
    
    public function getCountry() {
        return $this->country;
    }

    public function setCountry($country) {
        $this->country = $country;
        return $this;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getLine1() {
        return $this->line1;
    }

    public function getLine2() {
        return $this->line2;
    }

    public function getLine3() {
        return $this->line3;
    }

    public function getLine4() {
        return $this->line4;
    }

    public function getLine5() {
        return $this->line5;
    }

    public function getPostcode() {
        return $this->postcode;
    }

    public function getLat() {
        return $this->lat;
    }

    public function getLng() {
        return $this->lng;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getAddressId() {
        return $this->addressId;
    }

    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }
    
    public function getClient() {
        return $this->client;
    }

    
    public function setLine1($line1) {
        $this->line1 = $line1;
        return $this;
    }

    public function setLine2($line2) {
        $this->line2 = $line2;
        return $this;
    }

    public function setLine3($line3) {
        $this->line3 = $line3;
        return $this;
    }

    public function setLine4($line4) {
        $this->line4 = $line4;
        return $this;
    }

    public function setLine5($line5) {
        $this->line5 = $line5;
        return $this;
    }

    public function setPostcode($postcode) {
        $this->postcode = $postcode;
        return $this;
    }

    public function setLat($lat) {
        $this->lat = $lat;
        return $this;
    }

    public function setLng($lng) {
        $this->lng = $lng;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }
    
    public function setClient($client) {
        $this->client = $client;
        return $this;
    }

    public function setAddressId($addressId) {
        $this->addressId = $addressId;
        return $this;
    }

        
    
    
    public function assemble($sep=', ', $excludes=array()) {
        $address = array();
        if (!in_array('line1', $excludes)) {
            if ($this->getLine1()) {
                $address[] = $this->getLine1();
            }
        }

        if (!in_array('line2', $excludes)) {
            if ($this->getLine2()) {
                $address[] = $this->getLine2();
            }
        }

        if (!in_array('line3', $excludes)) {
            if ($this->getLine3()) {
                $address[] = $this->getLine3();
            }
        }

        if (!in_array('line4', $excludes)) {
            if ($this->getLine4()) {
                $address[] = $this->getLine4();
            }
        }

        if (!in_array('line5', $excludes)) {
            if ($this->getLine5()) {
                $address[] = $this->getLine5();
            }
        }

        if (!in_array('postcode', $excludes)) {
            if ($this->getPostcode()) {
                $address[] = $this->getPostcode();
            }
        }
        
        return implode($sep, $address);
    }

    
    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array()) 
    {
        //print_r($data);die();
        foreach ($data as $name=>$value) {
            $fn = "set{$name}";
            try {
                $this->$fn($value);
            } catch (\Exception $ex) {

            }
        }
    }/**/

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy() 
    {
        return get_object_vars($this);
    }
    
    
    protected $inputFilter;
    
    /**
     * set the input filter- only in forstructural and inheritance purposes
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    
    /**
     * 
     * @return Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
 
            $factory = new InputFactory();
            
            //example in google under search: "zf2 doctrine Album example"
            $inputFilter->add($factory->createInput(array(
                'name'     => 'postcode', // 'usr_name'
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 8,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'line1', // 'usr_name'
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 256,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'line2', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 256,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'line3', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 256,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'line4', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 256,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'line5', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 256,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'lat', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                ), 
            )));
 
            $inputFilter->add($factory->createInput(array(
                'name'     => 'lng', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                ), 
            )));
 
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 


}
