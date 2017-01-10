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
 * @ORM\Table(name="LiteIP_Device_Status")
 */
class LiteipDeviceStatus implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="Status_Text", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Status_Description", type="string", length=200, nullable=true)
     */
    private $description;


    /**
     * @var boolean
     *
     * @ORM\Column(name="Fault", type="boolean", nullable=false)
     */
    private $fault;

    /**
     * @var integer
     *
     * @ORM\Column(name="DeviceStatusID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $deviceStatusId;


    public function __construct()
    {
        $this->setWidth(0);
        $this->setHeight(0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function isFault()
    {
        return $this->fault;
    }

    /**
     * @param boolean $fault
     */
    public function setFault($fault)
    {
        $this->fault = $fault;
    }

    /**
     * @return int
     */
    public function getDeviceStatusId()
    {
        return $this->deviceStatusId;
    }

    /**
     * @param int $deviceStatusId
     */
    public function setDeviceStatusId($deviceStatusId)
    {
        $this->deviceStatusId = $deviceStatusId;
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
