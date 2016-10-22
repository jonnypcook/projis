<?php
namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;
use ZfcRbac\Identity\IdentityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass="Application\Repository\User")
 */
class User implements IdentityInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    private $config;
    
    
    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="User\Entity\HierarchicalRole")
     * @ORM\JoinTable(name="User_Role", joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="user_id")}, inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="role_id")})
     */
    private $roles;
    
    /**
     * @var string
     *
     * @ORM\Column(name="token_access", type="text", nullable=true)
     */
    private $token_access;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="google_id", type="string", length=128, nullable=true)
     */
    private $google_id;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="token_refresh", type="string", length=128, nullable=true)
     */
    private $token_refresh;


    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=100, nullable=false)
	 * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1, "max":30}})
     * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[a-zA-Z][a-zA-Z0-9_-]{0,24}$/"}})
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"Username:"})	 
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     * @Annotation\Attributes({"type":"password"})
     * @Annotation\Options({"label":"Password:"})	
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=false, unique=true)
	 * @Annotation\Type("Zend\Form\Element\Email")
     * @Annotation\Options({"label":"Your email address:"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="forename", type="string", length=60, nullable=false)
     * @Annotation\Options({"label":"Your forename:"})
     */
    private $forename;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=60, nullable=false)
     * @Annotation\Options({"label":"Your surname:"})
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_name", type="string", length=60, nullable=true)
     * @Annotation\Options({"label":"Your avatar name:"})
     */
    private $avatar_name;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="string", length=64, nullable=true)
     * @Annotation\Options({"label":"Your avatar name:"})
     */
    private $signature; //signature is userId.'zxdfcv45' md5'd ... always a png and stored in /resources/user/signature/


    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
	 * @Annotation\Type("Zend\Form\Element\Radio")
	 * @Annotation\Options({
	 * "label":"User Active:",
	 * "value_options":{"1":"Yes", "0":"No"}})
     */
    private $active;
    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="googleEnabled", type="boolean", nullable=false)
     */
    private $googleEnabled;
    

    /**
     * @var string
     *
     * @ORM\Column(name="secret_question", type="string", length=100, nullable=true)
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"User Question:"})
     */
    private $secretQuestion;

    /**
     * @var string
     *
     * @ORM\Column(name="secret_answer", type="string", length=100, nullable=true)
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"User Answer:"})
     */
    private $secretAnswer;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"User Picture:"})
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(name="password_salt", type="string", length=100, nullable=true)
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"Password Salt:"})
     */
    private $passwordSalt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     * @Annotation\Attributes({"type":"datetime","min":"2010-01-01T00:00:00Z","max":"2020-01-01T00:00:00Z","step":"1"})
     * @Annotation\Options({"label":"Registration Date:", "format":"Y-m-d\TH:iP"})
     */
    private $registrationDate; // = '2013-07-30 00:00:00'; // new \DateTime() - coses synatx error

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_expiry_date", type="datetime", nullable=true)
     */
    private $passwordExpiryDate; 

    /**
     * @var string
     *
     * @ORM\Column(name="registration_token", type="string", length=100, nullable=true)
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"Registration Token:"})
     */
    private $registrationToken;

    /**
     * @var boolean
     *
     * @ORM\Column(name="email_confirmed", type="boolean", nullable=false)
	 * @Annotation\Type("Zend\Form\Element\Radio")
	 * @Annotation\Options({
	 * "label":"User confirmed email:",
	 * "value_options":{"1":"Yes", "0":"No"}})
     */
    private $emailConfirmed;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Position")
     * @ORM\JoinColumn(name="user_position_id", referencedColumnName="user_position_id", nullable=false)
     */
    private $position; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="User\Entity\Company")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="company_id", nullable=true)
     */
    private $company;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="User\Entity\Department")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="department_id", nullable=true)
     */
    private $department;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
	 * @Annotation\Exclude()
     */
    private $userId;

	
    public function __construct()
	{
		$this->registrationDate = new \DateTime();
        $this->googleEnabled = false;
        
        $this->roles = new ArrayCollection();
	}
    
    public function getCompany() {
        return $this->company;
    }

    public function setCompany($company) {
        $this->company = $company;
        return $this;
    }

    
    /**
     * Set username
     *
     * @param string $username
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    public function getPasswordExpiryDate() {
        return $this->passwordExpiryDate;
    }

    public function setPasswordExpiryDate(\DateTime $passwordExpiryDate) {
        $this->passwordExpiryDate = $passwordExpiryDate;
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    public function getGoogleEnabled() {
        return $this->googleEnabled;
    }

    public function setGoogleEnabled($googleEnabled) {
        $this->googleEnabled = $googleEnabled;
        return $this;
    }

        
    public function getGoogle_id() {
        return $this->google_id;
    }

    public function getToken_access() {
        return $this->token_access;
    }

    public function getToken_refresh() {
        return $this->token_refresh;
    }

    public function setGoogle_id($google_id) {
        $this->google_id = $google_id;
        return $this;
    }

    public function setToken_refresh($token_refresh) {
        $this->token_refresh = $token_refresh;
        return $this;
    }

    public function setToken_access($token_access) {
        $this->token_access = $token_access;
        return $this;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    public function getForename() {
        return $this->forename;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getAvatar_name() {
        return $this->avatar_name;
    }

    public function setForename($forename) {
        $this->forename = $forename;
        return $this;
    }

    public function setSurname($surname) {
        $this->surname = $surname;
        return $this;
    }

    public function setAvatar_name($avatar_name) {
        $this->avatar_name = $avatar_name;
        return $this;
    }

    public function getPosition() {
        return $this->position;
    }

    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }
    
    public function getSignature() {
        return $this->signature;
    }

    public function setSignature($signature) {
        $this->signature = $signature;
        return $this;
    }

    
    public function getConfig() {
        return $this->config;
    }

    public function setConfig($config) {
        $this->config = $config;
        return $this;
    }

        
        
    /**
     * Set password
     *
     * @param string $password
     * @return Users
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }



    /**
     * Set active
     *
     * @param boolean $active
     * @return Users
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set secretQuestion
     *
     * @param string $secretQuestion
     * @return Users
     */
    public function setSecretQuestion($secretQuestion)
    {
        $this->secretQuestion= $secretQuestion;
    
        return $this;
    }

    /**
     * Get secretQuestion
     *
     * @return string 
     */
    public function getSecretQuestion()
    {
        return $this->secretQuestion;
    }

    /**
     * Set secretAnswer
     *
     * @param string $secretAnswer
     * @return Users
     */
    public function setSecretAnswer($secretAnswer)
    {
        $this->secretAnswer = $secretAnswer;
    
        return $this;
    }

    /**
     * Get secretAnswer
     *
     * @return string 
     */
    public function getSecretAnswer()
    {
        return $this->secretAnswer;
    }

    /**
     * Set picture
     *
     * @param string $picture
     * @return Users
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    
        return $this;
    }

    /**
     * Get picture
     *
     * @return string 
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set passwordSalt
     *
     * @param string $passwordSalt
     * @return Users
     */
    public function setPasswordSalt($passwordSalt)
    {
        $this->passwordSalt = $passwordSalt;
    
        return $this;
    }

    /**
     * Get passwordSalt
     *
     * @return string 
     */
    public function getPasswordSalt()
    {
        return $this->passwordSalt;
    }

    /**
     * Set registrationDate
     *
     * @param string $registrationDate
     * @return Users
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    
        return $this;
    }

    /**
     * Get registrationDate
     *
     * @return string 
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Set registrationToken
     *
     * @param string $registrationToken
     * @return Users
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;
    
        return $this;
    }

    /**
     * Get registrationToken
     *
     * @return string 
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Set emailConfirmed
     *
     * @param string $emailConfirmed
     * @return Users
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;
    
        return $this;
    }

    /**
     * Get emailConfirmed
     *
     * @return string 
     */
    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }
    
    
    /** 
     * get handle of user
     * @return string
     */
    public function getHandle() {
        $avatar = $this->getAvatar_name();
        if (!empty($avatar)) {
            return $avatar;
        }
        
        return ucwords(trim($this->getForename().' '.$this->getSurname()));
    }
    
    /** 
     * get fullname of user
     * @return string
     */
    public function getName() {
        return ucwords(trim($this->getForename().' '.$this->getSurname()));
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }    
    
    /**
     * Set the list of roles
     * @param Collection $roles
     */
    public function setRoles(Collection $roles)
    {
        $this->roles->clear();
        foreach ($roles as $role) {
            $this->roles[] = $role;
        }
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Add one role to roles list
     * @param \Rbac\Role\RoleInterface $role
     */
    public function addRole(\Rbac\Role\RoleInterface $role)
    {
        $this->roles[] = $role;
    }
    
    public function addConfigProperty($name, $value) {
        $config = $this->getConfig();
        if (!empty($config)) {
            $config = json_decode($config, true);
            $config[$name] = $value;
            $this->setConfig(json_encode($config));
        } else {
            $this->setConfig(json_encode(array($name=>$value)));
        }
    }
    
    
}
