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
 * @ORM\Table(name="Project_Property")
 * @ORM\Entity 
 */
class ProjectProperty implements InputFilterAwareInterface
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
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created; 

    /** 
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="properties") 
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)* 
     */
    protected $project;

    /** 
     * @ORM\ManyToOne(targetEntity="Application\Entity\Property", inversedBy="projects") 
     * @ORM\JoinColumn(name="property_id", referencedColumnName="property_id", nullable=true)* 
     */
    protected $property;

    /** 
     * @var string 
     * 
     * @ORM\Column(name="value", type="text", nullable=false ) 
     */ 
    private $value;
    
    public function getProject() {
        return $this->project;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }
        
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

      
    public function getProperty() {
        return $this->property;
    }

    public function setProperty($property) {
        $this->property = $property;
        return $this;
    }
     
    public function getCreated() {
        return $this->created;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
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
		$this->setCreated(new \DateTime());
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


