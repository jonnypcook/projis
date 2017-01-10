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
 * @ORM\Table(name="LiteIP_Device")
 */
class LiteipDevice implements InputFilterAwareInterface
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LastE3StatusDate", type="datetime", nullable=true)
     */
    private $LastE3StatusDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="IsE3", type="boolean", nullable=false)
     */
    private $IsE3;

    /**
     * @var integer
     *
     * @ORM\Column(name="ProfileID", type="integer", nullable=false)
     */
    private $ProfileID;

    /**
     * @var integer
     *
     * @ORM\Column(name="PosLeft", type="integer", nullable=false)
     */
    private $PosLeft;

    /**
     * @var integer
     *
     * @ORM\Column(name="PosTop", type="integer", nullable=false)
     */
    private $PosTop;

    /**
     * @var float
     *
     * @ORM\Column(name="DeviceSN", type="float", nullable=false)
     */
    private $DeviceSN;


    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\LiteipDeviceStatus")
     * @ORM\JoinColumn(name="DeviceStatusID", referencedColumnName="DeviceStatusID", nullable=true)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\LiteipDrawing")
     * @ORM\JoinColumn(name="DrawingID", referencedColumnName="DrawingID", nullable=false)
     */
    private $drawing;


    /**
     * @var integer
     *
     * @ORM\Column(name="DeviceID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $DeviceID;


    public function __construct()
    {
        $this->drawing = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getLastE3StatusDate()
    {
        return $this->LastE3StatusDate;
    }

    /**
     * @param \DateTime $LastE3StatusDate
     */
    public function setLastE3StatusDate($LastE3StatusDate)
    {
        $this->LastE3StatusDate = $LastE3StatusDate;
    }

    /**
     * @return boolean
     */
    public function isIsE3()
    {
        return $this->IsE3;
    }

    /**
     * @param boolean $IsE3
     */
    public function setIsE3($IsE3)
    {
        $this->IsE3 = $IsE3;
    }

    /**
     * @return int
     */
    public function getProfileID()
    {
        return $this->ProfileID;
    }

    /**
     * @param int $ProfileID
     */
    public function setProfileID($ProfileID)
    {
        $this->ProfileID = $ProfileID;
    }

    /**
     * @return int
     */
    public function getPosLeft()
    {
        return $this->PosLeft;
    }

    /**
     * @param int $PosLeft
     */
    public function setPosLeft($PosLeft)
    {
        $this->PosLeft = $PosLeft;
    }

    /**
     * @return int
     */
    public function getPosTop()
    {
        return $this->PosTop;
    }

    /**
     * @param int $PosTop
     */
    public function setPosTop($PosTop)
    {
        $this->PosTop = $PosTop;
    }

    /**
     * @return float
     */
    public function getDeviceSN()
    {
        return $this->DeviceSN;
    }

    /**
     * @param float $DeviceSN
     */
    public function setDeviceSN($DeviceSN)
    {
        $this->DeviceSN = $DeviceSN;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getDrawing()
    {
        return $this->drawing;
    }

    /**
     * @param int $drawing
     */
    public function setDrawing($drawing)
    {
        $this->drawing = $drawing;
    }

    /**
     * @return int
     */
    public function getDeviceID()
    {
        return $this->DeviceID;
    }

    /**
     * @param int $DeviceID
     */
    public function setDeviceID($DeviceID)
    {
        $this->DeviceID = $DeviceID;
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
