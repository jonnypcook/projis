<?php
namespace Project\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

/** 
 * @ORM\Entity 
 * @ORM\Table(name="Finance_Provider")
 */
class FinanceProvider
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

        /**
     * @var integer
     *
     * @ORM\Column(name="finance_provider_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $financeProviderId;

	
    public function __construct()
	{
	}

    public function getName() {
        return $this->name;
    }

    public function getFinanceProviderId() {
        return $this->financeProviderId;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }


}
