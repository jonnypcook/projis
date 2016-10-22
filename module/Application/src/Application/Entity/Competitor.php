<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/** 
 * @ORM\Table(name="Competitor")
 * @ORM\Entity 
 */
class Competitor implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", nullable=true)
     */
    private $url;
    

    /**
     * @var string
     *
     * @ORM\Column(name="strengths", type="text", nullable=true)
     */
    private $strengths;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="weaknesses", type="text", nullable=true)
     */
    private $weaknesses;
    
    
    
    /** 
     * @ORM\OneToMany(targetEntity="Project\Entity\ProjectCompetitor", mappedBy="competitor") 
     */
    protected $projects; 

    
    /**
     * @var integer
     *
     * @ORM\Column(name="competitor_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $competitorId;

	
    public function __construct()
	{
        $this->projects = new ArrayCollection();
	}
    
    public function getName() {
        return $this->name;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getStrengths() {
        return $this->strengths;
    }

    public function getWeaknesses() {
        return $this->weaknesses;
    }

    public function getProjects() {
        return $this->projects;
    }

    public function getCompetitorId() {
        return $this->competitorId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setUrl($url) {
        $this->url = $url;
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

    public function setProjects($projects) {
        $this->projects = $projects;
        return $this;
    }

    public function setCompetitorId($competitorId) {
        $this->competitorId = $competitorId;
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
            
            $inputFilter->add($factory->createInput(array(
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
                'name'     => 'url', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'  => 'Zend\Validator\Uri',
                    ),
                ), 
            )));
            
        }
 
        return $this->inputFilter;
    } 
    
    

}


