<?php
namespace Space\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/** 
 * @ORM\Table(name="System")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Space\Repository\System")
 */
class System implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=512, nullable=true)
     */
    private $label;

    
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
     * @var float
     * 
     * @ORM\Column(name="ppu_trial", type="decimal", scale=2, nullable=true)
     */
    private $ppuTrial;
    
    
    /**
     * @var float
     * 
     * @ORM\Column(name="ippu", type="decimal", scale=2, nullable=true)
     */
    private $ippu;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity;    
    
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="hours", type="integer", nullable=true)
     */
    private $hours;
    
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="legacyWatts", type="integer", nullable=true)
     */
    private $legacyWatts;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="legacyQuantity", type="integer", nullable=true)
     */
    private $legacyQuantity;    


    /**
     * @var float
     * 
     * @ORM\Column(name="legacyMcpu", type="decimal", scale=2, nullable=true)
     */
    private $legacyMcpu;

    
    /**
     * @var integer
     *
     * @ORM\Column(name="lux", type="integer", nullable=false)
     */
    private $lux;    
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="occupancy", type="integer", nullable=false)
     */
    private $occupancy;    
    

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean", nullable=false)
     */
    private $locked;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="attributes", type="text", nullable=true)
     */
    private $attributes;
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Space\Entity\Space")
     * @ORM\JoinColumn(name="space_id", referencedColumnName="space_id", nullable=false)
     */
    private $space; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Product\Entity\Product", inversedBy="systems")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=false)
     */
    private $product; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Product\Entity\Pricing")
     * @ORM\JoinColumn(name="pricing_id", referencedColumnName="pricing_id", nullable=true)
     */
    private $pricing; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Product\Entity\Legacy")
     * @ORM\JoinColumn(name="legacy_id", referencedColumnName="legacy_id", nullable=true)
     */
    private $legacy; 

    
    /**
     * @var integer
     *
     * @ORM\Column(name="system_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $systemId;

	
    public function __construct()
	{
        $this->setIppu(0);
        $this->setHours(0);
        $this->setLux(0);
        $this->setOccupancy(0);
        $this->setLegacyQuantity(0);
        $this->setLegacyWatts(0);
        $this->setLocked(false);
        $this->setPpuTrial(0);
        
        $this->product = new ArrayCollection();
        $this->project = new ArrayCollection();
        //$this->pricing = new ArrayCollection();
        $this->legacy = new ArrayCollection();
	}
    
    public function getPpuTrial() {
        return $this->ppuTrial;
    }

    public function setPpuTrial($ppuTrial) {
        $this->ppuTrial = $ppuTrial;
        return $this;
    }

    public function getPricing() {
        return $this->pricing;
    }

    public function setPricing($pricing) {
        $this->pricing = $pricing;
        return $this;
    }

            
    public function getLabel() {
        return $this->label;
    }

    public function getCpu() {
        return $this->cpu;
    }

    public function getPpu() {
        return $this->ppu;
    }

    public function getIppu() {
        return $this->ippu;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getHours() {
        return $this->hours;
    }

    public function getLegacyWatts() {
        return $this->legacyWatts;
    }

    public function getLegacyQuantity() {
        return $this->legacyQuantity;
    }

    public function getLegacyMcpu() {
        return $this->legacyMcpu;
    }

    public function getLux() {
        return $this->lux;
    }

    public function getOccupancy() {
        return $this->occupancy;
    }
    
    public function getLocked() {
        return $this->locked;
    }

    public function getSpace() {
        return $this->space;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getLegacy() {
        return $this->legacy;
    }

    public function getSystemId() {
        return $this->systemId;
    }
    
    public function setLabel($label) {
        $this->label = $label;
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

    public function setIppu($ippu) {
        $this->ippu = $ippu;
        return $this;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

    public function setHours($hours) {
        $this->hours = $hours;
        return $this;
    }

    public function setLegacyWatts($legacyWatts) {
        $this->legacyWatts = $legacyWatts;
        return $this;
    }

    public function setLegacyQuantity($legacyQuantity) {
        $this->legacyQuantity = $legacyQuantity;
        return $this;
    }

    public function setLegacyMcpu($legacyMcpu) {
        $this->legacyMcpu = $legacyMcpu;
        return $this;
    }

    public function setLux($lux) {
        $this->lux = $lux;
        return $this;
    }

    public function setOccupancy($occupancy) {
        $this->occupancy = $occupancy;
        return $this;
    }

    public function setLocked($locked) {
        $this->locked = $locked;
        return $this;
    }

    public function setSpace($space) {
        $this->space = $space;
        return $this;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

    public function setLegacy($legacy) {
        $this->legacy = $legacy;
        return $this;
    }

    public function setSystemId($systemId) {
        $this->systemId = $systemId;
        return $this;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function setAttributes($attributes) {
        if (is_array($attributes)) {
            $attributes = json_encode($attributes);
        }
        $this->attributes = $attributes;
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
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'product', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'legacy', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'pricing', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(),
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
                            'min'      => 0,
                            'inclusive' => false
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'hours', // 'usr_name'
                'required' => false,
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
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 168,
                            'inclusive' => true
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
                            'inclusive' => true // set to false if zero not allowed
                        ),
                    ),
                ),
            )));


            $inputFilter->add($factory->createInput(array(
                'name'     => 'cpu', // 'usr_name'
                'required' => false,
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
                'name'     => 'ppuTrial', // 'usr_name'
                'required' => false,
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
                'name'     => 'ippu', // 'usr_name'
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
                'name'     => 'legacyWatts', // 'usr_name'
                'required' => false,
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
                'name'     => 'legacyMcpu', // 'usr_name'
                'required' => false,
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
                'name'     => 'legacyQuantity', // 'usr_name'
                'required' => false,
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
                'name'     => 'lux', // 'usr_name'
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
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 90,
                            'inclusive' => true
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'occupancy', // 'usr_name'
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
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 90,
                            'inclusive' => true
                        ),
                    ),
                ),
            )));


            $inputFilter->add($factory->createInput(array(
                'name'     => 'label', // 'usr_name'
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
                            'max'      => 512,
                        ),
                    ),

                ),
            )));
            
            /*$inputFilter->add($factory->createInput(array(
                'name'     => 'name', // 'usr_name'
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
                            'max'      => 255,
                        ),
                    ),
                    
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'financeProvider', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'co2', // 'usr_name'
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
                'name'     => 'fuelTariff', // 'usr_name'
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
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 1,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
 
            $inputFilter->add($factory->createInput(array(
                'name'     => 'rpi', // 'usr_name'
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
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 1,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
 
            $inputFilter->add($factory->createInput(array(
                'name'     => 'epi', // 'usr_name'
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
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 1,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'mcd', // 'usr_name'
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
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 0.5,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
            
            /**/
 
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 

}


