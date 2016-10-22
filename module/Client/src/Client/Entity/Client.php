<?php
namespace Client\Entity;

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
 * @ORM\Table(name="Client")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Client\Repository\Client")
 */
class Client implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="regno", type="string", length=50, nullable=true)
     */
    private $regno;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var float
     *
     * @ORM\Column(name="fund", type="decimal", scale=2, nullable=false)
     */
    private $fund;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
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
     * @ORM\ManyToOne(targetEntity="Application\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=false)
     * @ORM\OrderBy({"surname" = "ASC"})
     *      */
    private $user; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Source")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="source_id", nullable=false)
     */
    private $source; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FinanceStatus")
     * @ORM\JoinColumn(name="finance_status_id", referencedColumnName="finance_status_id", nullable=true)
     */
    private $financeStatus; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="PaymentTerms")
     * @ORM\JoinColumn(name="payment_terms_id", referencedColumnName="payment_terms_id", nullable=true)
     */
    private $paymentTerms; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="Application\Entity\User") 
     * @ORM\JoinTable(name="Client_Collaborators", joinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="client_id")}, inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="user_id")})
     */
    private $collaborators;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $clientId;

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        $this->setFund(0);
        $this->paymentTerms= new ArrayCollection();
        $this->financeStatus= new ArrayCollection();
        $this->source = new ArrayCollection();
        $this->user = new ArrayCollection();
        $this->collaborators = new ArrayCollection();
	}
    
    public function getPaymentTerms() {
        return $this->paymentTerms;
    }

    public function setPaymentTerms($paymentTerms) {
        $this->paymentTerms = $paymentTerms;
        return $this;
    }

        
    public function getCollaborators() {
        return $this->collaborators;
    }

    public function setCollaborators($collaborators) {
        $this->collaborators->clear();
        foreach ($collaborators as $collaborator) {
            $this->collaborators[] = $collaborator;
        }
        
        return $this;
    }
    
    /**
     * Add one role to roles list
     * @param Collection $collaborators
     */
    public function addCollaborators(Collection $collaborators)
    {
        foreach ($collaborators as $collaborator) {
            $this->collaborators->add($collaborator);
        }
//        $this->collaborators[] = $collaborator;
    }
    
    /**
     * @param Collection $collaborators
     */
    public function removeCollaborators(Collection $collaborators)
    {
        foreach ($collaborators as $collaborator) {
            $this->collaborators->removeElement($collaborator);
        }
    }
        

    public function setClientId($clientId) {
        $this->clientId = $clientId;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getRegno() {
        return $this->regno;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getFund() {
        return $this->fund;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getUser() {
        return $this->user;
    }

    public function getSource() {
        return $this->source;
    }

    public function getFinanceStatus() {
        return $this->financeStatus;
    }

    public function getClientId() {
        return $this->clientId;
    }

   
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setRegno($regno) {
        $this->regno = $regno;
        return $this;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    public function setFund($fund) {
        $this->fund = $fund;
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

    public function setSource($source) {
        $this->source = $source;
        return $this;
    }

    public function setFinanceStatus($financeStatus) {
        $this->financeStatus = $financeStatus;
        return $this;
    }
    
    public function getNotes() {
        return $this->notes;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
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
            $inputFilter->add($factory->createInput(array(
                'name'     => 'name', // 'usr_name'
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
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'regno', // 'usr_name'
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
                            'min'      => 1,
                            'max'      => 50,
                        ),
                    ),
                ), 
            )));
            

            $inputFilter->add($factory->createInput(array(
                'name'     => 'url', // 'usr_name'
                'required' => false,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Zend\Validator\Uri',
                    ),
                ), 
            )));
            

            $inputFilter->add($factory->createInput(array(
                'name'     => 'financeStatus', // 'usr_name'
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'fund', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
 
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 



}


