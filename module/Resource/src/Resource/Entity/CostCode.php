<?php
namespace Resource\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/**
 * @ORM\Table(name="Cost_Code")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Resource\Repository\CostCode")
 */
class CostCode
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="cost_code_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $costCodeId;


    public function __construct()
    {
        $this->setName( '' );
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName( $name )
    {
        $this->name = $name;

        return $this;
    }

    public function setCostCodeId( $costCodeId )
    {
        $this->costCodeId = $costCodeId;

        return $this;
    }

    public function getCostCodeId()
    {
        return $this->costCodeId;
    }

    public function exchangeArray( $data )
    {
        $this->name = $data['name'];
    }
}


