<?php
namespace Job\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Table(name="Dispatch")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Job\Repository\Dispatch")
 */
class Dispatch 
{
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created; 
    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent", type="datetime", nullable=false)
     */
    private $sent; 
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", nullable=true)
     */
    private $reference;

    
    /**
     * @var string
     *
     * @ORM\Column(name="deliveredby", type="string", nullable=true)
     */
    private $deliveredby;

    
    /**
     * @var boolean
     *
     * @ORM\Column(name="revoked", type="boolean", nullable=false)
     */
    private $revoked;
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="address_id", nullable=true)
     */
    private $address; 
    

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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=true)
     */
    private $user; 
    

    /** 
     * @ORM\OneToMany(targetEntity="Job\Entity\DispatchProduct", mappedBy="dispatch") 
     */
    protected $products; 
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="dispatch_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $dispatchId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        $this->setRevoked(false);
        
        $this->project= new ArrayCollection();
        $this->address= new ArrayCollection();
        $this->products= new ArrayCollection();
        $this->user = new ArrayCollection();
	}
    
    public function getDeliveredby() {
        return $this->deliveredby;
    }

    public function setDeliveredby($deliveredby) {
        $this->deliveredby = $deliveredby;
        return $this;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

        
    public function getProject() {
        return $this->project;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function getRevoked() {
        return $this->revoked;
    }

    public function setRevoked($revoked) {
        $this->revoked = $revoked;
        return $this;
    }

        
    public function getProducts() {
        return $this->products;
    }

    public function setProducts($products) {
        $this->products = $products;
        return $this;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getSent() {
        return $this->sent;
    }

    public function getReference() {
        return $this->reference;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getDispatchId() {
        return $this->dispatchId;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setSent(\DateTime $sent) {
        $this->sent = $sent;
        return $this;
    }

    public function setReference($reference) {
        $this->reference = $reference;
        return $this;
    }

    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    public function setDispatchId($dispatchId) {
        $this->dispatchId = $dispatchId;
        return $this;
    }



}


