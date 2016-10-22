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
 * @ORM\Table(name="Space")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Space\Repository\Space")
 */
class Space implements InputFilterAwareInterface
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
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;
    

    /**
     * @var integer
     *
     * @ORM\Column(name="floor", type="integer", nullable=true)
     */
    private $floor;    

    
    /**
     * @var integer
     *
     * @ORM\Column(name="dimx", type="integer", nullable=true)
     */
    private $dimx;

    
    /**
     * @var integer
     *
     * @ORM\Column(name="dimy", type="integer", nullable=true)
     */
    private $dimy;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="dimh", type="integer", nullable=true)
     */
    private $dimh;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="root", type="boolean", nullable=false)
     */
    private $root;    
    

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    private $deleted;    
    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=false)
     */
    private $project; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Client\Entity\Building")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="building_id", nullable=true)
     */
    private $building; 
    

    /**
     * @var integer
     *
     * @ORM\Column(name="space_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $spaceId;
    
    
    /** 
     * @ORM\OneToMany(targetEntity="Job\Entity\Serial", mappedBy="space") 
     */
    protected $serials; 
    
  
    public function __construct()
	{
        $this->setRoot(false);
        $this->setDeleted(false);
        $this->client = new ArrayCollection();
        $this->sector = new ArrayCollection();
        $this->status = new ArrayCollection();
        $this->type = new ArrayCollection();
        $this->financeYears = new ArrayCollection();
        $this->financeProvider = new ArrayCollection();
		$this->setCreated(new \DateTime());
        
        $this->collaborators = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        
        $this->serials = new ArrayCollection();
	}
    
    public function getSerials() {
        return $this->serials;
    }

    public function setSerials($serials) {
        $this->serials = $serials;
        return $this;
    }
    
    public function getFloor() {
        return $this->floor;
    }

    public function getDimx() {
        return $this->dimx;
    }

    public function getDimy() {
        return $this->dimy;
    }

    public function getDimh() {
        return $this->dimh;
    }

    public function setFloor($floor) {
        $this->floor = $floor;
        return $this;
    }

    public function setDimx($dimx) {
        $this->dimx = $dimx;
        return $this;
    }

    public function setDimy($dimy) {
        $this->dimy = $dimy;
        return $this;
    }

    public function setDimh($dimh) {
        $this->dimh = $dimh;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getRoot() {
        return $this->root;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getProject() {
        return $this->project;
    }

    public function getBuilding() {
        return $this->building;
    }

    public function getSpaceId() {
        return $this->spaceId;
    }

        
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

    public function setRoot($root) {
        $this->root = $root;
        return $this;
    }
    
    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function setBuilding($building) {
        $this->building = $building;
        return $this;
    }

    public function setSpaceId($spaceId) {
        $this->spaceId = $spaceId;
        return $this;
    }

    public function getDeleted() {
        return $this->deleted;
    }

    public function setDeleted($deleted) {
        $this->deleted = $deleted;
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
                'name'     => 'building', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 

}


