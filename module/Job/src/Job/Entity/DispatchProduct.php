<?php
namespace Job\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/** 
 * @ORM\Table(name="Dispatch_Product")
 * @ORM\Entity 
 */
class DispatchProduct implements InputFilterAwareInterface
{
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity; 

    /** 
     * @ORM\ManyToOne(targetEntity="Product\Entity\Product", inversedBy="dispatches") 
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=true)* 
     */
    protected $product;

    /** 
     * @ORM\ManyToOne(targetEntity="Job\Entity\Dispatch", inversedBy="products") 
     * @ORM\JoinColumn(name="dispatch_id", referencedColumnName="dispatch_id", nullable=true)* 
     */
    protected $dispatch;

    
    public function getId() {
        return $this->id;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getDispatch() {
        return $this->dispatch;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

    public function setDispatch($dispatch) {
        $this->dispatch = $dispatch;
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

    public function __construct() {
		$this->product = new ArrayCollection();
        $this->dispatch = new ArrayCollection();
    }
    
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
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 
    
    

}


