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
 * @ORM\Table(name="Save")
 * @ORM\Entity 
 */
class Save implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="checksum", type="string", length=64, nullable=true)
     */
    private $checksum;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=false)
     */
    private $config;
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created; 
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="activated", type="datetime", nullable=false)
     */
    private $activated; 
    
    
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
     * @ORM\ManyToOne(targetEntity="Application\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=false)
     */
    private $user; 
    

    /**
     * @var integer
     *
     * @ORM\Column(name="save_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $saveId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
		$this->setActivated(new \DateTime());
        
        $this->project = new ArrayCollection();
        $this->user = new ArrayCollection();
        
	}
    
    public function getActivated() {
        return $this->activated;
    }

    public function setActivated(\DateTime $activated) {
        $this->activated = $activated;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getProject() {
        return $this->project;
    }

    public function getUser() {
        return $this->user;
    }

    public function getSaveId() {
        return $this->saveId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setConfig($config) {
        $this->config = $config;
        $this->setChecksum(md5($config));
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

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function setSaveId($saveId) {
        $this->saveId = $saveId;
        return $this;
    }

    public function getChecksum() {
        return $this->checksum;
    }

    public function setChecksum($checksum) {
        $this->checksum = $checksum;
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
    
    private $updated = false;
    
    public function getUpdated() {
        return $this->updated;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
        return $this;
    }



}


