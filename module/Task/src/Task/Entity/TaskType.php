<?php
namespace Task\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Task_Type")
 */
class TaskType
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="compatibility", type="integer", nullable=false)
     */
    private $compatibility;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    private $config;

    
    /**
     * @var integer
     *
     * @ORM\Column(name="task_type_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $taskTypeId;

	
    public function __construct()
	{

    }
    
    public function getCompatibility() {
        return $this->compatibility;
    }

    public function getConfig() {
        return $this->config;
    }

    public function setCompatibility($compatibility) {
        $this->compatibility = $compatibility;
        return $this;
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getTaskTypeId() {
        return $this->taskTypeId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setTaskTypeId($taskTypeId) {
        $this->taskTypeId = $taskTypeId;
        return $this;
    }




}
