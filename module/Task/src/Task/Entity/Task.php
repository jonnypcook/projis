<?php
namespace Task\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/** 
 * @ORM\Table(name="Task")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Task\Repository\Task")
 */
class Task implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    private $config;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created; 
    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="required", type="datetime", nullable=false)
     */
    private $required; 
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completed", type="datetime", nullable=true)
     */
    private $completed; 

    
    /**
     * @var integer
     *
     * @ORM\Column(name="progress", type="integer", nullable=false)
     */
    private $progress;    
    

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
     * @ORM\ManyToOne(targetEntity="Task\Entity\TaskType")
     * @ORM\JoinColumn(name="task_type_id", referencedColumnName="task_type_id", nullable=false)
     */
    private $taskType; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Task\Entity\TaskStatus")
     * @ORM\JoinColumn(name="task_status_id", referencedColumnName="task_status_id", nullable=false)
     */
    private $taskStatus; 
    
    
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
     * @ORM\ManyToMany(targetEntity="Application\Entity\User") 
     * @ORM\JoinTable(name="Task_User", joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="task_id")}, inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="user_id")})
     */
    private $users;
    

    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="Application\Entity\Activity") 
     * @ORM\JoinTable(name="Task_Activity", joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="task_id")}, inverseJoinColumns={@ORM\JoinColumn(name="activity_id", referencedColumnName="activity_id")})
     */
    private $activities;

    /**
     * @var integer
     *
     * @ORM\Column(name="task_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $taskId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
		$this->setRequired(new \DateTime());

        $this->progress = 0;
        $this->user = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->taskType = new ArrayCollection();
        $this->taskStatus = new ArrayCollection();
        $this->client = new ArrayCollection();
        $this->project = new ArrayCollection();
	}
    
    public function getProgress() {
        return $this->progress;
    }

    public function setProgress($progress) {
        $this->progress = $progress;
        return $this;
    }

        
    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

        
    public function getActivities() {
        return $this->activities;
    }

    public function setActivities($activities) {
        $this->activities->clear();
        foreach ($activities as $activity) {
            $this->activities[] = $activity;
        }
        
        return $this;
    }
    
    /**
     * Add one activity to activities list
     * @param Collection $activities
     */
    public function addActivities(Collection $activities)
    {
        foreach ($activities as $activity) {
            $this->activities->add($activity);
        }
    }
    
    /**
     * @param Collection $activities
     */
    public function removeActivities(Collection $activities)
    {
        foreach ($activities as $activity) {
            $this->activities->removeElement($activity);
        }
    }
    
    
    
    public function getUsers() {
        return $this->users;
    }

    public function setUsers($users) {
        $this->users->clear();
        foreach ($users as $user) {
            $this->users[] = $user;
        }
        
        return $this;
    }
    
    /**
     * Add one user to users list
     * @param Collection $users
     */
    public function addUsers(Collection $users)
    {
        foreach ($users as $user) {
            $this->users->add($user);
        }
    }
    
    /**
     * @param Collection $users
     */
    public function removeUsers(Collection $users)
    {
        foreach ($users as $user) {
            $this->users->removeElement($user);
        }
    }
    
    
    public function getCompleted() {
        return $this->completed;
    }

    public function setCompleted(\DateTime $completed) {
        $this->completed = $completed;
        return $this;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getRequired() {
        return $this->required;
    }

    public function getUser() {
        return $this->user;
    }

    public function getTaskType() {
        return $this->taskType;
    }

    public function getTaskStatus() {
        return $this->taskStatus;
    }

    public function getClient() {
        return $this->client;
    }

    public function getProject() {
        return $this->project;
    }

    public function getTaskId() {
        return $this->taskId;
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setRequired(\DateTime $required) {
        $this->required = $required;
        return $this;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function setTaskType($taskType) {
        $this->taskType = $taskType;
        return $this;
    }

    public function setTaskStatus($taskStatus) {
        $this->taskStatus = $taskStatus;
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

    public function setTaskId($taskId) {
        $this->taskId = $taskId;
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
                'name'     => 'users', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(), 
            )));
 
            $inputFilter->add($factory->createInput(array(
                'name'     => 'required', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\Validator\Date',
                        'options' => array (
                            'format' => 'Y-m-d H:i:s'
                        )
                    ),
                ),                 
            )));
 
 
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 



}


