<?php
namespace Product\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation; // !!!! Absolutely neccessary

/**
 * @ORM\Table(name="Board_Configuration_Board_Chain")
 * @ORM\Entity
 */
class BoardConfigurationBoardChain
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;


    /**
     * @ORM\ManyToOne(targetEntity="Product\Entity\BoardChain", inversedBy="configurations")
     * @ORM\JoinColumn(name="board_chain_id", referencedColumnName="board_chain_id", nullable=true)*
     */
    protected $boardChain;

    /**
     * @ORM\ManyToOne(targetEntity="Product\Entity\BoardConfiguration", inversedBy="chains")
     * @ORM\JoinColumn(name="board_configuration_id", referencedColumnName="board_configuration_id", nullable=true)*
     */
    protected $boardConfiguration;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer", nullable=false )
     */
    protected $count;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getBoardChain()
    {
        return $this->boardChain;
    }

    /**
     * @param mixed $boardChain
     */
    public function setBoardChain($boardChain)
    {
        $this->boardChain = $boardChain;
    }

    /**
     * @return mixed
     */
    public function getBoardConfiguration()
    {
        return $this->boardConfiguration;
    }

    /**
     * @param mixed $boardConfiguration
     */
    public function setBoardConfiguration($boardConfiguration)
    {
        $this->boardConfiguration = $boardConfiguration;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
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

    public function __construct() {
        $this->setCount(1);
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }





}


