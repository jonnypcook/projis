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
 * @ORM\Table(name="Activity")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Application\Repository\Activity")
 */
class Activity implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;


    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created; 
    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDt", type="datetime", nullable=true)
     */
    private $startDt; 
    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDt", type="datetime", nullable=true)
     */
    private $endDt; 
    

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
     * @ORM\ManyToOne(targetEntity="Application\Entity\ActivityType")
     * @ORM\JoinColumn(name="activity_type_id", referencedColumnName="activity_type_id", nullable=false)
     */
    private $activityType; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Client\Entity\Client")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="client_id", nullable=true)
     */
    private $client; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true)
     */
    private $project; 
    

    /**
     * @var integer
     *
     * @ORM\Column(name="activity_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $activityId;
    
    
    public function __construct()
	{
		$this->setCreated(new \DateTime());

        $this->user = new ArrayCollection();
        $this->activityType= new ArrayCollection();
        $this->client = new ArrayCollection();
        $this->project = new ArrayCollection();
	}
    
    
    public function getStartDt() {
        return $this->startDt;
    }

    public function getEndDt() {
        return $this->endDt;
    }

    public function setStartDt(\DateTime $startDt) {
        $this->startDt = $startDt;
        return $this;
    }

    public function setEndDt(\DateTime $endDt) {
        $this->endDt = $endDt;
        return $this;
    }

        
    public function getData() {
        return $this->data;
    }

    public function getNote() {
        return $this->note;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getUser() {
        return $this->user;
    }

    public function getActivityType() {
        return $this->activityType;
    }

    public function getClient() {
        return $this->client;
    }

    public function getProject() {
        return $this->project;
    }

    public function getActivityId() {
        return $this->activityId;
    }

    
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    public function setNote($note) {
        $this->note = $note;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function setActivityType($activityType) {
        $this->activityType = $activityType;
        return $this;
    }

    public function setClient($client) {
        $this->client = $client;
        return $this;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function setActivityId($activityId) {
        $this->activityId = $activityId;
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


