<?php
namespace Task\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Task_Status")
 */
class TaskStatus
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=32, nullable=false)
     */
    private $icon;


    /**
     * @var integer
     *
     * @ORM\Column(name="task_status_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $taskStatusId;

	
    public function __construct()
	{

    }
    
    public function getIcon() {
        return $this->icon;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getTaskStatusId() {
        return $this->taskStatusId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setTaskStatusId($taskStatusId) {
        $this->taskStatusId = $taskStatusId;
        return $this;
    }





}
