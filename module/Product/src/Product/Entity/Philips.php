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
 * @ORM\Entity 
 * @ORM\Table(name="Product_Philips")
 */
class Philips implements InputFilterAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="nc", type="string", length=15, nullable=false)
     */
    private $nc;

    /**
     * @var integer
     *
     * @ORM\Column(name="eoc", type="integer", nullable=false)
     */
    private $eoc;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", length=100, nullable=false)
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;
    
    
    /**
     * @var float
     *
     * @ORM\Column(name="ppu_target_min", type="decimal", scale=2, nullable=false)
     */
    private $targetMin;

    
    /**
     * @var float
     *
     * @ORM\Column(name="ppu_target_max", type="decimal", scale=2, nullable=false)
     */
    private $targetMax;

    
    /**
     * @var float
     *
     * @ORM\Column(name="ppu_net_trade", type="decimal", scale=2, nullable=false)
     */
    private $netTrade;


    /**
     * @var float
     *
     * @ORM\Column(name="cpu", type="decimal", scale=2, nullable=false)
     */
    private $cpu;

    /**
     * @var float
     *
     * @ORM\Column(name="ppu", type="decimal", scale=2, nullable=false)
     */
    private $ppu;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="PhilipsBrand")
     * @ORM\JoinColumn(name="product_philips_brand_id", referencedColumnName="product_philips_brand_id", nullable=false)
     */
    private $brand; 
    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="PhilipsCategory")
     * @ORM\JoinColumn(name="product_philips_category_id", referencedColumnName="product_philips_category_id", nullable=false)
     */
    private $category; 
    
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=true)
     */
    private $product; 
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="product_philips_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $philipsId;
    

	
    public function __construct()
	{
		$this->setCreated(new \DateTime());
        $this->brand = new ArrayCollection();
        $this->category = new ArrayCollection();
        $this->product = new ArrayCollection();
	}
    
    
    public function getProduct() {
        return $this->product;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

        
    public function getCategory() {
        return $this->category;
    }

    public function setCategory($category) {
        $this->category = $category;
        return $this;
    }

        
    public function getNc() {
        return $this->nc;
    }

    public function getEoc() {
        return $this->eoc;
    }

    public function getModel() {
        return $this->model;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getTargetMin() {
        return $this->targetMin;
    }

    public function getTargetMax() {
        return $this->targetMax;
    }

    public function getNetTrade() {
        return $this->netTrade;
    }

    public function getCpu() {
        return $this->cpu;
    }

    public function getPpu() {
        return $this->ppu;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getBrand() {
        return $this->brand;
    }

    public function getPhilipsId() {
        return $this->philipsId;
    }

    public function setNc($nc) {
        $this->nc = $nc;
        return $this;
    }

    public function setEoc($eoc) {
        $this->eoc = $eoc;
        return $this;
    }

    public function setModel($model) {
        $this->model = $model;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setTargetMin($targetMin) {
        $this->targetMin = $targetMin;
        return $this;
    }

    public function setTargetMax($targetMax) {
        $this->targetMax = $targetMax;
        return $this;
    }

    public function setNetTrade($netTrade) {
        $this->netTrade = $netTrade;
        return $this;
    }

    public function setCpu($cpu) {
        $this->cpu = $cpu;
        return $this;
    }

    public function setPpu($ppu) {
        $this->ppu = $ppu;
        return $this;
    }

    public function setCreated(\DateTime $created) {
        $this->created = $created;
        return $this;
    }

    public function setBrand($brand) {
        $this->brand = $brand;
        return $this;
    }


    public function setPhilipsId($philipsId) {
        $this->philipsId = $philipsId;
        return $this;
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
            
            /**/
 
            $this->inputFilter = $inputFilter;        
        }
 
        return $this->inputFilter;
    } 
}

/*

CREATE TABLE Product_Philips (product_philips_id INT AUTO_INCREMENT NOT NULL, product_philips_brand_id INT NOT NULL, product_philips_category_id INT NOT NULL, nc VARCHAR(15) NOT NULL, eoc INT NOT NULL, model VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, ppu_target_min NUMERIC(10, 2) NOT NULL, ppu_target_max NUMERIC(10, 2) NOT NULL, ppu_net_trade NUMERIC(10, 2) NOT NULL, cpu NUMERIC(10, 2) NOT NULL, ppu NUMERIC(10, 2) NOT NULL, created DATETIME NOT NULL, INDEX IDX_47E56A885CBFBD2F (product_philips_brand_id), INDEX IDX_47E56A88691E70FB (product_philips_category_id), PRIMARY KEY(product_philips_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE Product_Philips_Brand (product_philips_brand_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, prefix VARCHAR(3) DEFAULT NULL, UNIQUE INDEX UNIQ_E2BEE9D65E237E06 (name), PRIMARY KEY(product_philips_brand_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE Product_Philips_Category (product_philips_category_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, prefix VARCHAR(3) DEFAULT NULL, PRIMARY KEY(product_philips_category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE Product_Philips ADD CONSTRAINT FK_47E56A885CBFBD2F FOREIGN KEY (product_philips_brand_id) REFERENCES Product_Philips_Brand (product_philips_brand_id);
ALTER TABLE Product_Philips ADD CONSTRAINT FK_47E56A88691E70FB FOREIGN KEY (product_philips_category_id) REFERENCES Product_Philips_Category (product_philips_category_id);
CREATE TABLE `Philips_Upload` (
  `product_philips_category_id` int(11) DEFAULT NULL,
  `brand` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `nc` bigint(11) DEFAULT NULL,
  `eoc` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `model` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `description` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `ppu_target_min` float(8,2) DEFAULT NULL,
  `ppu_target_max` float(8,2) DEFAULT NULL,
  `ppu_net_trade` float(8,2) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_philips_brand_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=utf8;
ALTER TABLE Product_Philips ADD product_id INT DEFAULT NULL;
ALTER TABLE Product_Philips ADD CONSTRAINT FK_47E56A884584665A FOREIGN KEY (product_id) REFERENCES Product (product_id);
CREATE INDEX IDX_47E56A884584665A ON Product_Philips (product_id);

INSERT INTO `Product_Philips_Brand` (`product_philips_brand_id`, `name`, `prefix`)
VALUES
	(22, 'DayZone', NULL),
	(23, 'Smartform', NULL),
	(24, 'Power Balance', NULL),
	(25, 'Coreview', NULL),
	(26, 'CoreLine', NULL),
	(27, 'Maxos', NULL),
	(28, 'SkyRibbon', NULL),
	(29, 'Smart Balance', NULL),
	(30, 'Lumistone', NULL),
	(31, 'Celino', NULL),
	(32, 'Arano', NULL);

INSERT INTO `Product_Philips_Category` (`product_philips_category_id`, `name`, `prefix`)
VALUES
	(1, 'Recessed', NULL),
	(2, 'Suspended', NULL),
	(3, 'Surface Mounted', NULL),
	(4, 'High/Low Bay', NULL),
	(5, 'Light Line', NULL),
	(6, 'Wall Mounted', NULL),
	(7, 'Downlights', NULL),
	(8, 'Multiple Lighting', NULL),
	(9, 'Projectors', NULL),
	(10, 'Cove & Contour', NULL),
	(11, 'Waterproof & Cleanroom', NULL),
	(12, 'Free Standing', NULL),
	(13, 'Bollards', NULL),
	(14, 'Pole', NULL),
	(15, 'Floodlight', NULL),
	(16, 'Direct View', NULL),
	(17, 'Markers, Grazers & Underwater', NULL),
	(18, 'Tunnels & Underpass', NULL),
	(19, 'Under Canopy', NULL),
	(20, 'Amenity', NULL);

/**/

