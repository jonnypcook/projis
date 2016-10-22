<?php
namespace Product\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

/**
 * @ORM\Entity 
 * @ORM\Table(name="Product_Document")
 */
class Document implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false, unique=true)
     */
    private $name;

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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Project\Entity\DocumentExtension")
     * @ORM\JoinColumn(name="document_extension_id", referencedColumnName="document_extension_id", nullable=false)
     */
    private $extension; 
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="product_document_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $documentId;
    
    
    /** 
     * @ORM\OneToMany(targetEntity="Space\Entity\System", mappedBy="product") 
     */
    protected $systems; 

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        $this->extension = new ArrayCollection();
	}
    
    public function getCreated() {
        return $this->created;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function getDocumentId() {
        return $this->documentId;
    }

    public function getSystems() {
        return $this->systems;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setExtension($extension) {
        $this->extension = $extension;
        return $this;
    }

    public function setDocumentId($documentId) {
        $this->documentId = $documentId;
        return $this;
    }

    public function setSystems($systems) {
        $this->systems = $systems;
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
            /**/
 
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 
}


