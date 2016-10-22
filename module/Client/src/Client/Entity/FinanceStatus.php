<?php
namespace Client\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Finance_Status")
 */
class FinanceStatus
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="colour", type="string", length=15)
     */
    private $colour;
    

        /**
     * @var integer
     *
     * @ORM\Column(name="finance_status_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $financeStatusId;

	
    public function __construct()
	{
	}

    public function getName() {
        return $this->name;
    }

    public function getColour() {
        return $this->colour;
    }

    public function getFinanceStatusId() {
        return $this->financeStatusId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setColour($colour) {
        $this->colour = $colour;
        return $this;
    }

    public function setFinanceStatusId($financeStatusId) {
        $this->financeStatusId = $financeStatusId;
        return $this;
    }



}
