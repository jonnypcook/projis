<?php
namespace Product\Entity;
use Doctrine\ORM\Mapping as ORM;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/**
 * @ORM\Entity
 * @ORM\Table(name="Phosphor")
 */
class Phosphor
{
    /**
     * @var integer
     *
     * @ORM\Column(name="colour", type="integer", nullable=false)
     */
    private $colour;

    /**
     * @var float
     *
     * @ORM\Column(name="length", type="decimal", scale=2, nullable=false)
     */
    private $length;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var boolean
     *
     * @ORM\Column(name="default", type="boolean", nullable=false)
     */
    private $default;

    /**
     * @var integer
     *
     * @ORM\Column(name="phosphor_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $phosphorId;


    public function __construct()
    {
        $this->setEnabled(true);
        $this->setDefault(false);
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param boolean $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return int
     */
    public function getColour()
    {
        return $this->colour;
    }

    /**
     * @param int $colour
     */
    public function setColour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param float $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return int
     */
    public function getPhosphorId()
    {
        return $this->phosphorId;
    }

    /**
     * @param int $phosphorId
     */
    public function setPhosphorId($phosphorId)
    {
        $this->phosphorId = $phosphorId;
    }




}
