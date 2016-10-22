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
 * @ORM\Table(name="Project_Competitor")
 * @ORM\Entity 
 */
class ProjectCompetitor implements InputFilterAwareInterface
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
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="competitors") 
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)* 
     */
    protected $project;

    /** 
     * @ORM\ManyToOne(targetEntity="Application\Entity\Competitor", inversedBy="projects") 
     * @ORM\JoinColumn(name="competitor_id", referencedColumnName="competitor_id", nullable=true)* 
     */
    protected $competitor;

    /** 
     * @var string 
     * 
     * @ORM\Column(name="strategy", type="text", nullable=true ) 
     */ 
    private $strategy;
    
    /** 
     * @var string 
     * 
     * @ORM\Column(name="response", type="text", nullable=true ) 
     */ 
    private $response;
    
    /** 
     * @var string 
     * 
     * @ORM\Column(name="strengths", type="text", nullable=true ) 
     */ 
    private $strengths;
    
    /** 
     * @var string 
     * 
     * @ORM\Column(name="weaknesses", type="text", nullable=true ) 
     */ 
    private $weaknesses;
    
    
    
    public function getId() {
        return $this->id;
    }

    public function getProject() {
        return $this->project;
    }

    public function getCompetitor() {
        return $this->competitor;
    }

    public function getStrategy() {
        return $this->strategy;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function setCompetitor($competitor) {
        $this->competitor = $competitor;
        return $this;
    }

    public function setStrategy($strategy) {
        $this->strategy = $strategy;
        return $this;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getStrengths() {
        return $this->strengths;
    }

    public function getWeaknesses() {
        return $this->weaknesses;
    }

    public function setResponse($response) {
        $this->response = $response;
        return $this;
    }

    public function setStrengths($strengths) {
        $this->strengths = $strengths;
        return $this;
    }

    public function setWeaknesses($weaknesses) {
        $this->weaknesses = $weaknesses;
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
            
            //example in google under search: "zf2 doctrine Album example"
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 
    
    

}


