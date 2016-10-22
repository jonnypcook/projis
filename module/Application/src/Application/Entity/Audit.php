<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Application\Entity\User;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/** 
 * @ORM\Table(name="Audit")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Application\Repository\Audit")
 */
class Audit implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created; 
    

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
     * @ORM\ManyToOne(targetEntity="Application\Entity\AuditType")
     * @ORM\JoinColumn(name="audit_type_id", referencedColumnName="audit_type_id", nullable=false)
     */
    private $auditType; 
    
    
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
     * @ORM\ManyToOne(targetEntity="Space\Entity\Space")
     * @ORM\JoinColumn(name="space_id", referencedColumnName="space_id", nullable=true)
     */
    private $space; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Product\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=true)
     */
    private $product; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\DocumentCategory")
     * @ORM\JoinColumn(name="document_category_id", referencedColumnName="document_category_id", nullable=true)
     */
    private $documentCategory; 
    

    /**
     * @var integer
     *
     * @ORM\Column(name="audit_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $auditId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        $this->user = new ArrayCollection();
        $this->auditType= new ArrayCollection();
        $this->client = new ArrayCollection();
        $this->project = new ArrayCollection();
        $this->space = new ArrayCollection();
        $this->product = new ArrayCollection();
        $this->documentCategory = new ArrayCollection();
	}
    
    public function getData() {
        return $this->data;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getUser() {
        return $this->user;
    }

    public function getAuditType() {
        return $this->auditType;
    }

    public function getClient() {
        return $this->client;
    }

    public function getProject() {
        return $this->project;
    }

    public function getAuditId() {
        return $this->auditId;
    }

    
    public function setData($data) {
        $this->data = $data;
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

    public function setAuditType($auditType) {
        $this->auditType = $auditType;
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

    
    public function getSpace() {
        return $this->space;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getDocumentCategory() {
        return $this->documentCategory;
    }

    public function setSpace($space) {
        $this->space = $space;
        return $this;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

    public function setDocumentCategory($document) {
        $this->documentCategory = $document;
        return $this;
    }

    public function setAuditId($auditId) {
        $this->auditId = $auditId;
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


