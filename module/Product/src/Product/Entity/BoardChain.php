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
 * @ORM\Table(name="Board_Chain")
 * @ORM\Entity
 */
class BoardChain
{
    /**
     * @var integer
     *
     * @ORM\Column(name="depth", type="integer", nullable=false)
     */
    private $depth;

    /**
     * @var float
     *
     * @ORM\Column(name="length", type="decimal", scale=2, nullable=false)
     */
    private $length;

    /**
     * @var integer
     *
     * @ORM\Column(name="board_chain_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $boardChainId;


    /**
     * @ORM\OneToMany(targetEntity="Product\Entity\BoardConfigurationBoardChain", mappedBy="boardChain")
     */
    protected $configurations;


    public function __construct()
    {
        $this->configurations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }



    /**
     * @return ArrayCollection|int
     */
    public function getConfigurations() {
        return $this->configurations;
    }

    /**
     * @param $configurations
     * @return $this
     */
    public function setConfigurations($configurations) {
        $this->configurations->clear();
        foreach ($configurations as $configuration) {
            $this->configurations[] = $configuration;
        }

        return $this;
    }

    /**
     * Add one role to roles list
     * @param Collection $configurations
     */
    public function addConfigurations(Collection $configurations)
    {
        foreach ($configurations as $configuration) {
            $this->configurations->add($configuration);
        }
    }

    /**
     * @param Collection $configurations
     */
    public function removeConfigurations(Collection $configurations)
    {
        foreach ($configurations as $configuration) {
            $this->configurations->removeElement($configuration);
        }
    }

    /**
     * @param $configurationId
     * @return bool
     */
    public function hasConfiguration ($configurationId) {
        foreach ($this->configurations as $configuration) {
            if ($configurationId == $configuration->getBoardConfigurationId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * get the configuration array used in architectural calculations
     * @return array
     */
    public function getConfigurationArray () {
        if (empty($this->getConfigurations())) {
            return array();
        }

        $items = array();
        foreach ($this->getConfigurations() as $configuration) {
            $items[$configuration->getBoardConfiguration()->getConfiguration()] = $configuration->getCount();
        }

        return array (
            $this->getLength(),
            $items,
            $this->getDepth()
        );
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
    public function getBoardChainId()
    {
        return $this->boardChainId;
    }

    /**
     * @param int $boardChainId
     */
    public function setBoardChainId($boardChainId)
    {
        $this->boardChainId = $boardChainId;
    }



}


