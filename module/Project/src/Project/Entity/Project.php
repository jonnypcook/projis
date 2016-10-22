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

use Application\Entity\User;
/** 
 * @ORM\Table(name="Project")
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Project\Repository\Project")
 */
class Project implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="co2", type="decimal", scale=5, nullable=false)
     */
    private $co2;    
    

    /**
     * @var float
     *
     * @ORM\Column(name="fueltariff", type="decimal", scale=5, nullable=false)
     */
    private $fuelTariff;    
    

    /**
     * @var float
     *
     * @ORM\Column(name="rpi", type="decimal", scale=5, nullable=false)
     */
    private $rpi;    
    

    /**
     * @var float
     *
     * @ORM\Column(name="epi", type="decimal", scale=5, nullable=false)
     */
    private $epi;    
    

    /**
     * @var float
     *
     * @ORM\Column(name="mcd", type="decimal", scale=5, nullable=false)
     */
    private $mcd;    
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="factor_prelim", type="decimal", scale=2, nullable=false)
     */
    private $factorPrelim;    
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="factor_overhead", type="decimal", scale=2, nullable=false)
     */
    private $factorOverhead;    
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="factor_management", type="decimal", scale=2, nullable=false)
     */
    private $factorManagement;    
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="eca", type="decimal", scale=5, nullable=false)
     */
    private $eca;    
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="maintenance", type="decimal", scale=2, nullable=false)
     */
    private $maintenance;    
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="maintenance_led", type="decimal", scale=2, nullable=true)
     */
    private $maintenanceLed;    
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="maintenance_led_year", type="integer", nullable=true)
     */
    private $maintenanceLedYear;
    
    /**
     * @var float
     *
     * @ORM\Column(name="carbon", type="decimal", scale=2, nullable=false)
     */
    private $carbon;    
    

    /**
     * @var integer
     *
     * @ORM\Column(name="model", type="integer", nullable=true)
     */
    private $model;    
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="rating", type="integer", nullable=false)
     */
    private $rating;  
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="propertyCount", type="integer", nullable=true)
     */
    private $propertyCount;    
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="weighting", type="integer", nullable=false)
     */
    private $weighting;    
    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="test", type="boolean", nullable=false)
     */
    private $test;
    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="retrofit", type="boolean", nullable=false)
     */
    private $retrofit;
    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="ibp", type="boolean", nullable=false)
     */
    private $ibp;
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created; 
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="contracted", type="datetime", nullable=true)
     */
    private $contracted; 
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="installed", type="datetime", nullable=true)
     */
    private $installed; 
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completed", type="datetime", nullable=true)
     */
    private $completed; 
    

    /**
     * @var boolean
     *
     * @ORM\Column(name="cancelled", type="boolean", nullable=false)
     */
    private $cancelled;
    
    
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
     * @ORM\JoinColumn(name="client_id", referencedColumnName="client_id", nullable=false)
     */
    private $client; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sector")
     * @ORM\JoinColumn(name="project_sector_id", referencedColumnName="project_sector_id", nullable=false)
     */
    private $sector; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="project_status_id", referencedColumnName="project_status_id", nullable=false)
     */
    private $status; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Type")
     * @ORM\JoinColumn(name="project_type_id", referencedColumnName="project_type_id", nullable=false)
     */
    private $type; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FinanceYears")
     * @ORM\JoinColumn(name="finance_years_id", referencedColumnName="finance_years_id", nullable=false)
     */
    private $financeYears; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FinanceProvider")
     * @ORM\JoinColumn(name="finance_provider_id", referencedColumnName="finance_provider_id", nullable=true)
     */
    private $financeProvider; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="Contact\Entity\Contact", inversedBy="projects") 
     * @ORM\JoinTable(name="Project_Contacts", joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="project_id")}, inverseJoinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id")})
     */
    private $contacts;
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="Application\Entity\User") 
     * @ORM\JoinTable(name="Project_Collaborators", joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="project_id")}, inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="user_id")})
     */
    private $collaborators;
    
    /** 
     * @ORM\OneToMany(targetEntity="Project\Entity\ProjectProperty", mappedBy="project") 
     */
    protected $properties; 
    
    /** 
     * @ORM\OneToMany(targetEntity="Project\Entity\ProjectCompetitor", mappedBy="project") 
     */
    protected $competitors; 
    
    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="Application\Entity\State") 
     * @ORM\JoinTable(name="Project_State", joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="project_id")}, inverseJoinColumns={@ORM\JoinColumn(name="state_id", referencedColumnName="state_id")})
     */
    private $states;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="project_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $projectId;

    
    /** 
     * @ORM\OneToMany(targetEntity="Job\Entity\Serial", mappedBy="project") 
     */
    protected $serials;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expected_date", type="datetime", nullable=true)
     */
    private $expectedDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="exclude_from_reporting", type="boolean", nullable=true)
     */
    private $excludeReporting;

    public function __construct()
	{
        $this->setCo2(0.5246);
        $this->setFuelTariff(0.12);
        $this->setRpi(0.035);
        $this->setEpi(0.1);
        $this->setFactorManagement(0);
        $this->setFactorOverhead(0);
        $this->setFactorPrelim(0);
        $this->setMcd(0);
        $this->setEca(0);
        $this->setCarbon(0);
        $this->setFund(0);
        $this->setModel(5);
        $this->setMaintenance(0);
        $this->setMaintenanceLed(0);
        $this->setMaintenanceLedYear(6);
        $this->setWeighting(0);
        $this->setTest(false);
        $this->setIbp(false);
		$this->setCreated(new \DateTime());
        $this->setExpectedDate(new \DateTime());
        
        $this->setRating(0);
        
        $this->setCancelled(false);
        $this->setRetrofit(true);
        $this->setExcludeReporting(false);
        
        $this->setPropertyCount(1);
        
        $this->serials = new ArrayCollection();

        $this->client = new ArrayCollection();
        $this->sector = new ArrayCollection();
        $this->status = new ArrayCollection();
        $this->type = new ArrayCollection();
        $this->financeYears = new ArrayCollection();
        $this->financeProvider = new ArrayCollection();
        
        $this->states = new ArrayCollection();
        $this->competitors = new ArrayCollection();
        $this->properties = new ArrayCollection();
        $this->collaborators = new ArrayCollection();
        $this->contacts = new ArrayCollection();
	}

    public function getExcludeReporting(){
        return $this->excludeReporting;
    }

    public function setExcludeReporting( $excludeReporting){
        $this->excludeReporting = $excludeReporting;
        return $this;
    }

    public function getExpectedDate(){
        return $this->expectedDate;
    }

    public function setExpectedDate( $expectedDate = null ){
        $this->expectedDate =  $expectedDate;
        return $this;
    }

    public function getPropertyCount() {
        return $this->propertyCount;
    }

    public function setPropertyCount($propertyCount) {
        $this->propertyCount = $propertyCount;
        return $this;
    }

        
    public function getMaintenanceLed() {
        return $this->maintenanceLed;
    }

    public function getMaintenanceLedYear() {
        return $this->maintenanceLedYear;
    }

    public function setMaintenanceLed($maintenanceLed) {
        $this->maintenanceLed = $maintenanceLed;
        return $this;
    }

    public function setMaintenanceLedYear($maintenanceLedYear) {
        $this->maintenanceLedYear = $maintenanceLedYear;
        return $this;
    }

        
    public function getRating() {
        return $this->rating;
    }

    public function setRating($rating) {
        $this->rating = $rating;
        return $this;
    }

        
    public function getSerials() {
        return $this->serials;
    }

    public function setSerials($serials) {
        $this->serials = $serials;
        return $this;
    }

        
   
    public function getRetrofit() {
        return $this->retrofit;
    }

    public function setRetrofit($retrofit) {
        $this->retrofit = $retrofit;
        return $this;
    }

            
    public function getCancelled() {
        return $this->cancelled;
    }

    public function setCancelled($cancelled) {
        $this->cancelled = $cancelled;
        return $this;
    }

        
    public function getName() {
        return $this->name;
    }

    public function getCo2() {
        return $this->co2;
    }

    public function getFuelTariff() {
        return $this->fuelTariff;
    }

    public function getRpi() {
        return $this->rpi;
    }
    
    public function getEpi() {
        return $this->epi;
    }
    
    public function getFactorPrelim() {
        return $this->factorPrelim;
    }

    public function getFactorOverhead() {
        return $this->factorOverhead;
    }

    public function getFactorManagement() {
        return $this->factorManagement;
    }
    
    public function getMcd() {
        return $this->mcd;
    }

    public function getEca() {
        return $this->eca;
    }

    public function getCarbon() {
        return $this->carbon;
    }

    public function getFund() {
        return $this->fund;
    }

    public function getModel() {
        return $this->model;
    }
    
    public function getWeighting() {
        return $this->weighting;
    }
    
    public function getMaintenance() {
        return $this->maintenance;
    }

    public function getTest() {
        return $this->test;
    }

    public function getIbp() {
        return $this->ibp;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getContracted() {
        return $this->contracted;
    }

    public function getInstalled() {
        return $this->installed;
    }

    public function getCompleted() {
        return $this->completed;
    }
    
    public function getNotes() {
        return $this->notes;
    }

    
    public function getClient() {
        return $this->client;
    }

    public function getSector() {
        return $this->sector;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getType() {
        return $this->type;
    }

    public function getFinanceYears() {
        return $this->financeYears;
    }

    public function getFinanceProvider() {
        return $this->financeProvider;
    }

    
    public function getProjectId() {
        return $this->projectId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setCo2($co2) {
        $this->co2 = $co2;
        return $this;
    }

    public function setFuelTariff($fuelTariff) {
        $this->fuelTariff = $fuelTariff;
        return $this;
    }

    public function setRpi($rpi) {
        $this->rpi = $rpi;
        return $this;
    }
    
    public function setEpi($epi) {
        $this->epi = $epi;
        return $this;
    }

    public function setFactorPrelim($factorPrelim) {
        $this->factorPrelim = $factorPrelim;
        return $this;
    }

    public function setFactorOverhead($factorOverhead) {
        $this->factorOverhead = $factorOverhead;
        return $this;
    }

    public function setFactorManagement($factorManagement) {
        $this->factorManagement = $factorManagement;
        return $this;
    }

        public function setMcd($mcd) {
        $this->mcd = $mcd;
        return $this;
    }

    public function setEca($eca) {
        $this->eca = $eca;
        return $this;
    }

    public function setCarbon($carbon) {
        $this->carbon = $carbon;
        return $this;
    }

    public function setFund($fund) {
        $this->fund = $fund;
        return $this;
    }

    public function setModel($model) {
        $this->model = $model;
        return $this;
    }
    
    public function setWeighting($weighting) {
        $this->weighting = $weighting;
        return $this;
    }

    public function setTest($test) {
        $this->test = $test;
        return $this;
    }

    public function setIbp($ibp) {
        $this->ibp = $ibp;
        return $this;
    }

    public function setCreated($created = null) {
        $this->created = $created;
        return $this;
    }

    public function setContracted($contracted = null) {
        $this->contracted = $contracted;
        return $this;
    }

    public function setInstalled(\DateTime $installed) {
        $this->installed = $installed;
        return $this;
    }

    public function setCompleted($completed = null) {
        $this->completed = $completed;
        return $this;
    }
    
    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

    public function setProjectId($projectId) {
        $this->projectId = $projectId;
        return $this;
    }

    public function setMaintenance($maintenance) {
        $this->maintenance = $maintenance;
        return $this;
    }

    public function setClient($client) {
        $this->client = $client;
        return $this;
    }

    public function setSector($sector) {
        $this->sector = $sector;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setFinanceYears($financeYears) {
        $this->financeYears = $financeYears;
        return $this;
    }

    public function setFinanceProvider($financeProvider) {
        $this->financeProvider = $financeProvider;
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
    
    
    public function getStates() {
        return $this->states;
    }

    public function setStates($states) {
        $this->states->clear();
        foreach ($states as $state) {
            $this->states[] = $state;
        }
        
        return $this;
    }
    
    /**
     * Add one role to roles list
     * @param Collection $collaborators
     */
    public function addStates(Collection $states)
    {
        foreach ($states as $state) {
            $this->states->add($state);
        }
    }
    
    /**
     * @param Collection $collaborators
     */
    public function removeStates(Collection $states)
    {
        foreach ($states as $state) {
            $this->states->removeElement($state);
        }
    }
    
    public function hasState ($stateId) {
        foreach ($this->states as $state) {
            if ($stateId==$state->getStateId()) {
                return true;
            }
        }
        
        return false;
    }




    public function findProperty($propertyId, $first=false) {
        $return = array();
        foreach ($this->properties as $property) {
            if ($propertyId==$property->getProperty()->getPropertyId()) {
               $return[]=$property;
               if ($first) {
                   break;
               }
            }
        }
        
        return $return;
    }
    
    public function getProperties() {
        return $this->properties;
    }

    public function setProperties($properties) {
        $this->properties->clear();
        foreach ($properties as $property) {
            $this->properties[] = $property;
        }
        
        return $this;
    }
    
    /**
     * Add one role to roles list
     * @param Collection $collaborators
     */
    public function addProperties(Collection $properties)
    {
        foreach ($properties as $property) {
            $this->properties->add($property);
        }
    }
    
    /**
     * @param Collection $collaborators
     */
    public function removeProperties(Collection $properties)
    {
        foreach ($properties as $property) {
            $this->properties->removeElement($property);
        }
    }
    
    
    public function getContacts() {
        return $this->contacts;
    }

    public function setContacts($contacts) {
        $this->contacts->clear();
        foreach ($contacts as $contact) {
            $this->contacts[] = $contact;
        }
        
        return $this;
    }
    
    /**
     * Add one role to roles list
     * @param Collection $collaborators
     */
    public function addContacts(Collection $contacts)
    {
        foreach ($contacts as $contact) {
            $this->contacts->add($contact);
        }
    }
    
    /**
     * @param Collection $collaborators
     */
    public function removeContacts(Collection $contacts)
    {
        foreach ($contacts as $contact) {
            $this->contacts->removeElement($contact);
        }
    }
    
    public function getCompetitors() {
        return $this->competitors;
    }

    public function setCompetitors($competitors) {
        $this->competitors->clear();
        foreach ($competitors as $competitor) {
            $this->competitors[] = $competitor;
        }
        
        return $this;
    }
    
    /**
     * Add one role to roles list
     * @param Collection $collaborators
     */
    public function addCompetitors(Collection $competitors)
    {
        foreach ($competitors as $competitor) {
            $this->competitors->add($competitor);
        }
    }
    
    /**
     * @param Collection $collaborators
     */
    public function removeCompetitors(Collection $competitors)
    {
        foreach ($competitors as $competitor) {
            $this->competitors->removeElement($competitor);
        }
    }
    
 
    public function isFinanced() {
        return (($this->getFinanceProvider() instanceof \Project\Entity\FinanceProvider) && ($this->getFinanceYears()>0));
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
            if ($value==null) {
                continue;
            }
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
                    /*array(
                        'name'		=> 'DoctrineModule\Validator\ObjectExists',
                        'options' => array(
                            'object_repository' => $sm->get('doctrine.entitymanager.orm_default')->getRepository('Application\Entity\User'),
                            'fields'            => 'username'
                        ),

                    ),/**/
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'financeProvider', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'rating', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'model', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'contacts', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'states', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'co2', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
 
            $inputFilter->add($factory->createInput(array(
                'name'     => 'maintenance', // 'usr_name'
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
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'fuelTariff', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => false
                        ),
                    ),
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 1,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
 
            $inputFilter->add($factory->createInput(array(
                'name'     => 'rpi', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => false
                        ),
                    ),
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 1,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
 
            $inputFilter->add($factory->createInput(array(
                'name'     => 'epi', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => false
                        ),
                    ),
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 1,
                            'inclusive' => false
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'mcd', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\Float',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => -0.5,
                            'inclusive' => true
                        ),
                    ),
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 0.5,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'weighting', // 'usr_name'
                'required' => true,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => 'Int',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => true
                        ),
                    ),
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 100,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'propertyCount', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => 'Int',
                    ),
                    array(
                        'name'    => 'GreaterThan',
                        'options' => array(
                            'min'      => 0,
                            'inclusive' => false
                        ),
                    ),
                    array(
                        'name'    => 'LessThan',
                        'options' => array(
                            'max'      => 9999,
                            'inclusive' => true
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'installed', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(
                    array(
                        'name'    => '\Zend\Validator\Date',
                        'options' => array(
                            'format' => 'd/m/Y',
                        ),
                    ),
                ), 
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'maintenanceLed', // 'usr_name'
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
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'maintenanceLedYear', // 'usr_name'
                'required' => false,
                'filters'  => array(),
                'validators' => array(), 
            )));
            
            
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 
    


}


