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
 * @ORM\Table(name="DocumentExtension")
 * @ORM\Entity 
 */
class DocumentExtension implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=100, nullable=false)
     */
    private $extension;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="header", type="string", length=256, nullable=false)
     */
    private $header;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="document_extension_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $documentExtensionId;

	
    public function __construct()
	{
        
	}
    
    public function getDocumentExtensionId() {
        return $this->documentExtensionId;
    }

    public function setDocumentExtensionId($documentExtensionId) {
        $this->documentExtensionId = $documentExtensionId;
        return $this;
    }

        
    public function getExtension() {
        return $this->extension;
    }

    public function getHeader() {
        return $this->header;
    }

    

    public function setExtension($extension) {
        $this->extension = $extension;
        return $this;
    }

    public function setHeader($header) {
        $this->header = $header;
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
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 

}
