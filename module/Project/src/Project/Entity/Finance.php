<?php
namespace Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/** 
 * @ORM\Table(name="Finance")
 * @ORM\Entity 
 */
class Finance implements InputFilterAwareInterface
{
    /**
     * @var float
     *
     * @ORM\Column(name="factor", type="decimal", scale=5, nullable=false)
     */
    private $factor;    
    

    /**
     * @var float
     *
     * @ORM\Column(name="min", type="decimal", scale=2, nullable=false)
     */
    private $min;    
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="max", type="decimal", scale=2, nullable=false)
     */
    private $max;    
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FinanceYears")
     * @ORM\JoinColumn(name="finance_years_id", referencedColumnName="finance_years_id", nullable=false)
     */
    private $financeYears; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Client\Entity\FinanceStatus")
     * @ORM\JoinColumn(name="finance_status_id", referencedColumnName="finance_status_id", nullable=true)
     */
    private $financeStatus; 
    

    /**
     * @var integer
     *
     * @ORM\Column(name="finance_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $financeId;

	
    public function __construct()
	{
        $this->financeYears = new ArrayCollection();
        $this->financeStatus = new ArrayCollection();
	}
    
    
    public function getFactor() {
        return $this->factor;
    }

    public function getMin() {
        return $this->min;
    }

    public function getMax() {
        return $this->max;
    }

    public function getFinanceYears() {
        return $this->financeYears;
    }

    public function getFinanceStatus() {
        return $this->financeStatus;
    }

    public function getFinanceId() {
        return $this->financeId;
    }

    public function setFactor($factor) {
        $this->factor = $factor;
        return $this;
    }

    public function setMin($min) {
        $this->min = $min;
        return $this;
    }

    public function setMax($max) {
        $this->max = $max;
        return $this;
    }

    public function setFinanceYears($financeYears) {
        $this->financeYears = $financeYears;
        return $this;
    }
    
    public function setFinanceStatus($financeStatus) {
        $this->financeStatus = $financeStatus;
        return $this;
    }

    public function setFinanceId($financeId) {
        $this->financeId = $financeId;
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
            
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 

}


