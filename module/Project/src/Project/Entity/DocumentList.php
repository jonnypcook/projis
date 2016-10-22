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
 * @ORM\Table(name="DocumentList")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Project\Repository\DocumentList")
 */
class DocumentList implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=256, nullable=false)
     */
    private $filename;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=32, nullable=true)
     */
    private $hash;
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created; 
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private $size;  
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="subid", type="integer", nullable=true)
     */
    private $subid;  
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\DocumentExtension")
     * @ORM\JoinColumn(name="document_extension_id", referencedColumnName="document_extension_id", nullable=false)
     */
    private $extension; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\DocumentCategory")
     * @ORM\JoinColumn(name="document_category_id", referencedColumnName="document_category_id", nullable=false)
     */
    private $category; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=true)
     */
    private $user; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=false)
     */
    private $project; 
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    private $config;
    

    
    /**
     * @var integer
     *
     * @ORM\Column(name="document_list_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $documentListId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        $this->project = new ArrayCollection();
        $this->user = new ArrayCollection();
        $this->extension= new ArrayCollection();
        $this->category = new ArrayCollection();
	}
    
    public function getFilename() {
        return $this->filename;
    }

    public function getHash() {
        return $this->hash;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getSize() {
        return $this->size;
    }

    public function getProject() {
        return $this->project;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getDocumentListId() {
        return $this->documentListId;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
        return $this;
    }

    public function setHash($hash) {
        $this->hash = $hash;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setSize($size) {
        $this->size = $size;
        return $this;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

    public function setDocumentListId($documentListId) {
        $this->documentListId = $documentListId;
        return $this;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function setExtension($extension) {
        $this->extension = $extension;
        return $this;
    }

        
    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function getCategory() {
        return $this->category;
    }

    public function setCategory($category) {
        $this->category = $category;
        return $this;
    }
    
    public function getSubid() {
        return $this->subid;
    }

    public function setSubid($subid) {
        $this->subid = $subid;
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


