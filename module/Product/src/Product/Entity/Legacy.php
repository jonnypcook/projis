<?php
namespace Product\Entity;
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
 * @ORM\Entity(repositoryClass="Product\Repository\Legacy")
 * @ORM\Table(name="Legacy")
 */
class Legacy implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="pwr_item", type="decimal", scale=4, nullable=false)
     */
    private $pwr_item;

    /**
     * @var float
     *
     * @ORM\Column(name="pwr_ballast", type="decimal", scale=4, nullable=false)
     */
    private $pwr_ballast;

    /**
     * @var boolean
     *
     * @ORM\Column(name="emergency", type="boolean", nullable=false)
     */
    private $emergency;

    /**
     * @var string
     *
     * @ORM\Column(name="dim_item", type="string", length=64, nullable=true)
     */
    private $dim_item;

    /**
     * @var string
     *
     * @ORM\Column(name="dim_unit", type="string", length=64, nullable=true)
     */
    private $dim_unit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="legacy_category_id", referencedColumnName="legacy_category_id", nullable=false)
     */
    private $category; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Product\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=true)
     */
    private $product; 
    

    /**
     * @var string
     *
     * @ORM\Column(name="attributes", type="text", nullable=true)
     */
    private $attributes;


    /**
     * @var integer
     *
     * @ORM\Column(name="legacy_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $legacyId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        $this->setQuantity(1);
        $this->setEmergency(false);

        $this->category = new ArrayCollection();
        //$this->product = new ArrayCollection();
	}
    
    public function getProduct() {
        return $this->product;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

        
    public function getDescription() {
        return $this->description;
    }


    public function getQuantity() {
        return $this->quantity;
    }

    public function getPwr_item() {
        return $this->pwr_item;
    }

    public function getPwr_ballast() {
        return $this->pwr_ballast;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getAttributes() {
        return $this->attributes;
    }
    
    public function getDim_item() {
        return $this->dim_item;
    }

    public function getDim_unit() {
        return $this->dim_unit;
    }
        
    public function getEmergency() {
        return $this->emergency;
    }

    public function getLegacyId() {
        return $this->legacyId;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

    public function setPwr_item($pwr_item) {
        $this->pwr_item = $pwr_item;
        return $this;
    }

    public function setPwr_ballast($pwr_ballast) {
        $this->pwr_ballast = $pwr_ballast;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setCategory($category) {
        $this->category = $category;
        return $this;
    }

    public function setAttributes($attributes) {
        $this->attributes = $attributes;
        return $this;
    }
    
    public function setDim_item($dim_item) {
        $this->dim_item = $dim_item;
        return $this;
    }

    public function setDim_unit($dim_unit) {
        $this->dim_unit = $dim_unit;
        return $this;
    }

    
    public function setEmergency($emergency) {
        $this->emergency = $emergency;
        return $this;
    }
    
    public function setLegacyId($legacyId) {
        $this->legacyId = $legacyId;
        return $this;
    }

    
    public function getTotalPwr() {
        return ($this->getQuantity()*$this->getPwr_item()) + $this->getPwr_ballast();
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
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'description', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'pwr_item', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'pwr_ballast', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'quantity', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => 'Int',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 1,
                            'inclusive' => true
                        ),
                    ),
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 10,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
            
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 
    
}


