<?php
namespace Product\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Table(name="Board_Configuration")
 * @ORM\Entity
 */
class BoardConfiguration
{
    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="string", nullable=false)
     */
    private $configuration;


    /**
     * @var float
     *
     * @ORM\Column(name="length", type="decimal", scale=2, nullable=false)
     */
    private $length;


    /**
     * @var boolean
     *
     * @ORM\Column(name="chainable", type="boolean", nullable=false)
     */
    private $chainable;

    /**
     * @var integer
     *
     * @ORM\Column(name="board_configuration_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $boardConfigurationId;




    public function __construct()
    {
        $this->chains = new ArrayCollection();
    }

    /**
     * @return boolean
     */
    public function isChainable()
    {
        return $this->chainable;
    }

    /**
     * @param boolean $chainable
     */
    public function setChainable($chainable)
    {
        $this->chainable = $chainable;
    }


    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
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
     * @return int
     */
    public function getBoardConfigurationId()
    {
        return $this->boardConfigurationId;
    }

    /**
     * @param int $boardConfigurationId
     */
    public function setBoardConfigurationId($boardConfigurationId)
    {
        $this->boardConfigurationId = $boardConfigurationId;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        foreach ($data as $name=>$value) {
            $fn = "set{$name}";
            try {
                $this->$fn($value);
            } catch (\Exception $ex) {

            }
        }
    }/**/

}


