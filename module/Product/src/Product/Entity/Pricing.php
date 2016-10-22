<?php
namespace Product\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/**
 * @ORM\Entity 
 * @ORM\Table(name="Product_Pricing")
 */
class Pricing implements InputFilterAwareInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="min", type="integer", nullable=false)
     */
    private $min;

    /**
     * @var integer
     *
     * @ORM\Column(name="max", type="integer", nullable=false)
     */
    private $max;

    /**
     * @var float
     *
     * @ORM\Column(name="cpu", type="decimal", scale=2, nullable=false)
     */
    private $cpu;

    /**
     * @var float
     *
     * @ORM\Column(name="ppu", type="decimal", scale=2, nullable=false)
     */
    private $ppu;


    /** 
     * @ORM\ManyToOne(targetEntity="Product\Entity\Product", inversedBy="pricepoints") 
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=false)
     */
    protected $product;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="pricing_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $pricingId;
    
    
    /** 
     * @ORM\OneToMany(targetEntity="Space\Entity\System", mappedBy="product") 
     */
    protected $systems; 

	
    public function __construct()
	{
        $this->product = new ArrayCollection();
	}
    
    public function getMin() {
        return $this->min;
    }

    public function getMax() {
        return $this->max;
    }

    public function getCpu() {
        return $this->cpu;
    }

    public function getPpu() {
        return $this->ppu;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getPricingId() {
        return $this->pricingId;
    }

    public function getSystems() {
        return $this->systems;
    }

    public function setMin($min) {
        $this->min = $min;
        return $this;
    }

    public function setMax($max) {
        $this->max = $max;
        return $this;
    }

    public function setCpu($cpu) {
        $this->cpu = $cpu;
        return $this;
    }

    public function setPpu($ppu) {
        $this->ppu = $ppu;
        return $this;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

    public function setPricingId($pricingId) {
        $this->pricingId = $pricingId;
        return $this;
    }

    public function setSystems($systems) {
        $this->systems = $systems;
        return $this;
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
            
            /**/
            $inputFilter->add($factory->createInput(array(
                'name'     => 'cpu', // 'usr_name'
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
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'ppu', // 'usr_name'
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
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'min', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => 'Int',
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
                'name'     => 'max', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => 'Int',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
 
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 
}


