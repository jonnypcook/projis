<?php
namespace Contact\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Table(name="Contact")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Contact\Repository\Contact")
 */
class Contact implements InputFilterAwareInterface
{
    
    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="Project\Entity\Project", mappedBy="contacts") 
     */
    private $projects;
    
    /**
     * @var string
     *
     * @ORM\Column(name="forename", type="string", length=32, nullable=true)
     */
    private $forename;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=32, nullable=false)
     */
    private $surname;

    
    /**
     * @var string
     *
     * @ORM\Column(name="telephone1", type="string", length=20, nullable=true)
     */
    private $telephone1;

    
    /**
     * @var string
     *
     * @ORM\Column(name="telephone2", type="string", length=20, nullable=true)
     */
    private $telephone2;

    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=64, nullable=true)
	 * @Annotation\Type("Zend\Form\Element\Email")
     * @Annotation\Options({"label":"Your email address:"})
     */
    private $email;
    

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=128, nullable=true)
     */
    private $position;

    
    /**
     * @var string
     *
     * @ORM\Column(name="keywinresult", type="text", nullable=true)
     */
    private $keywinresult;

    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created; 
    

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;
    

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
     * @ORM\ManyToOne(targetEntity="Influence")
     * @ORM\JoinColumn(name="contact_influence_id", referencedColumnName="contact_influence_id", nullable=true)
     */
    private $influence; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Mode")
     * @ORM\JoinColumn(name="contact_mode_id", referencedColumnName="contact_mode_id", nullable=true)
     */
    private $mode; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Title")
     * @ORM\JoinColumn(name="title_id", referencedColumnName="title_id")
     */
    private $title; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BuyingType")
     * @ORM\JoinColumn(name="buyingtype_id", referencedColumnName="buyingtype_id", nullable=true)
     */
    private $buyingType; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="address_id", nullable=true)
     */
    private $address; 
    

    /**
     * @var integer
     *
     * @ORM\Column(name="contact_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $contactId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        
        $this->client= new ArrayCollection();
        $this->title= new ArrayCollection();
        $this->influence= new ArrayCollection();
        $this->mode= new ArrayCollection();
        $this->buyingType = new ArrayCollection();
        $this->address = new ArrayCollection();
	}
    
    public function getKeywinresult() {
        return $this->keywinresult;
    }

    public function setKeywinresult($keywinresult) {
        $this->keywinresult = $keywinresult;
        return $this;
    }

        
    public function getInfluence() {
        return $this->influence;
    }

    public function getMode() {
        return $this->mode;
    }

    public function setInfluence($influence) {
        $this->influence = $influence;
        return $this;
    }

    public function setMode($mode) {
        $this->mode = $mode;
        return $this;
    }

        
    public function getForename() {
        return $this->forename;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getTelephone1() {
        return $this->telephone1;
    }

    public function getTelephone2() {
        return $this->telephone2;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPosition() {
        return $this->position;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getBuyingType() {
        return $this->buyingType;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getContactId() {
        return $this->contactId;
    }
    
    public function getClient() {
        return $this->client;
    }
    
    public function getNotes() {
        return $this->notes;
    }

    
    
    public function setForename($forename) {
        $this->forename = $forename;
        return $this;
    }

    public function setSurname($surname) {
        $this->surname = $surname;
        return $this;
    }

    public function setTelephone1($telephone1) {
        $this->telephone1 = $telephone1;
        return $this;
    }

    public function setTelephone2($telephone2) {
        $this->telephone2 = $telephone2;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

    public function setContactId($contactId) {
        $this->contactId = $contactId;
        return $this;
    }

        
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function setBuyingType($buyingType) {
        $this->buyingType = $buyingType;
        return $this;
    }

    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    public function setClient($client) {
        $this->client = $client;
        return $this;
    }
    
        
    public function getName() {
        $name = '';
        if ($this->getTitle() instanceof Title) {
            $name.=$this->getTitle()->getName();
        }
        
        $name.=' '.$this->getForename().' '.$this->getSurname();
        
        return ucwords(trim($name));
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
                'name'     => 'forename', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'max'      => 32,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'surname', // 'usr_name'
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'max'      => 32,
                        ),
                    ),
                ), 
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'position', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'max'      => 128,
                        ),
                    ),
                ), 
            )));
            
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'telephone1', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'max'      => 20,
                        ),
                    ),
                ), 
            )));
            
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'telephone2', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'max'      => 20,
                        ),
                    ),
                ), 
            )));
            
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'email', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(
                    array (
                        'name' => 'Zend\Validator\EmailAddress'
                    )
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'titleId', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'buyingtypeId', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'addressId', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'influenceId', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'modeId', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 


}


