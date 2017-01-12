<?php
namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/**
 * @ORM\Entity
 * @ORM\Table(name="LiteIP_Project")
 */
class LiteipProject implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="ProjectDescription", type="string", length=100, nullable=false)
     */
    private $ProjectDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="PostCode", type="string", length=10, nullable=true)
     */
    private $PostCode;


    /**
     * @var integer
     *
     * @ORM\Column(name="CustomerGroup", type="integer", nullable=true)
     */
    private $CustomerGroup;

    /**
     * @var boolean
     *
     * @ORM\Column(name="TestSite", type="boolean", nullable=false)
     */
    private $TestSite;

    /**
     * @var integer
     *
     * @ORM\Column(name="ProjectID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $ProjectID;


    public function __construct()
    {
        $this->setTestSite(false);
    }

    /**
     * @return boolean
     */
    public function isTestSite()
    {
        return $this->TestSite;
    }

    /**
     * @param boolean $TestSite
     */
    public function setTestSite($TestSite)
    {
        $this->TestSite = $TestSite;
    }



    /**
     * @return string
     */
    public function getProjectDescription()
    {
        return $this->ProjectDescription;
    }

    /**
     * @param string $ProjectDescription
     */
    public function setProjectDescription($ProjectDescription)
    {
        $this->ProjectDescription = $ProjectDescription;
    }

    /**
     * @return string
     */
    public function getPostCode()
    {
        return $this->PostCode;
    }

    /**
     * @param string $PostCode
     */
    public function setPostCode($PostCode)
    {
        $this->PostCode = $PostCode;
    }

    /**
     * @return int
     */
    public function getCustomerGroup()
    {
        return $this->CustomerGroup;
    }

    /**
     * @param int $CustomerGroup
     */
    public function setCustomerGroup($CustomerGroup)
    {
        $this->CustomerGroup = $CustomerGroup;
    }

    /**
     * @return int
     */
    public function getProjectID()
    {
        return $this->ProjectID;
    }

    /**
     * @param int $ProjectID
     */
    public function setProjectID($ProjectID)
    {
        $this->ProjectID = $ProjectID;
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
