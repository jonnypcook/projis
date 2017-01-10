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
 * @ORM\Table(name="LiteIP_Drawing")
 */
class LiteipDrawing implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="Drawing", type="string", length=500, nullable=false)
     */
    private $Drawing;


    /**
     * @var boolean
     *
     * @ORM\Column(name="Activated", type="boolean", nullable=false)
     */
    private $Activated;

    /**
     * @var float
     *
     * @ORM\Column(name="Width", type="float", nullable=false)
     */
    private $Width;

    /**
     * @var float
     *
     * @ORM\Column(name="Height", type="float", nullable=false)
     */
    private $Height;


    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\LiteipProject")
     * @ORM\JoinColumn(name="ProjectID", referencedColumnName="ProjectID", nullable=false)
     */
    private $project;


    /**
     * @var integer
     *
     * @ORM\Column(name="DrawingID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $DrawingID;


    public function __construct()
    {
        $this->setWidth(0);
        $this->setHeight(0);
        $this->setActivated(false);
        $this->project = new ArrayCollection();
    }

    /**
     * @return boolean
     */
    public function isActivated()
    {
        return $this->Activated;
    }

    /**
     * @param boolean $Activated
     */
    public function setActivated($Activated)
    {
        $this->Activated = $Activated;
    }


    /**
     * @return string
     */
    public function getDrawing()
    {
        return $this->Drawing;
    }

    /**
     * @param string $Drawing
     */
    public function setDrawing($Drawing)
    {
        $this->Drawing = $Drawing;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->Width;
    }

    /**
     * @param float $Width
     */
    public function setWidth($Width)
    {
        $this->Width = $Width;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->Height;
    }

    /**
     * @param float $Height
     */
    public function setHeight($Height)
    {
        $this->Height = $Height;
    }

    /**
     * @return int
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param int $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return int
     */
    public function getDrawingID()
    {
        return $this->DrawingID;
    }

    /**
     * @param int $DrawingID
     */
    public function setDrawingID($DrawingID)
    {
        $this->DrawingID = $DrawingID;
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
