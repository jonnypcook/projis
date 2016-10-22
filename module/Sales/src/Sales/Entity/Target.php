<?php
namespace Sales\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Zend\Form\Annotation; // !!!! Absolutely neccessary

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Application\Entity\User;

/**
 * @ORM\Table(name="Target")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Sales\Repository\Target")
 */
class Target implements InputFilterAwareInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="target_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $targetId;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_1", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth1;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_2", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth2;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_3", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth3;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_4", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth4;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_5", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth5;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_6", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth6;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_7", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth7;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_8", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth8;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_9", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth9;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_10", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth10;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_11", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth11;

    /**
     * @var float
     *
     * @ORM\Column(name="sales_month_12", type="decimal", scale=2, nullable=true)
     */
    private $salesMonth12;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_1", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth1;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_2", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth2;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_3", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth3;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_4", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth4;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_5", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth5;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_6", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth6;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_7", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth7;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_8", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth8;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_9", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth9;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_10", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth10;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_11", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth11;

    /**
     * @var float
     *
     * @ORM\Column(name="margin_month_12", type="decimal", scale=2, nullable=true)
     */
    private $marginMonth12;


    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", scale=2, nullable=true)
     */
    private $year;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=false)
     * @ORM\OrderBy({"surname" = "ASC"})
     *      */
    private $user;

    public function __construct()
    {
        $this->setSalesMonth1( 0 );
        $this->setMarginMonth1( 0 );

        $this->setSalesMonth2( 0 );
        $this->setMarginMonth2( 0 );

        $this->setSalesMonth3( 0 );
        $this->setMarginMonth3( 0 );

        $this->setSalesMonth4( 0 );
        $this->setMarginMonth4( 0 );

        $this->setSalesMonth5( 0 );
        $this->setMarginMonth5( 0 );

        $this->setSalesMonth6( 0 );
        $this->setMarginMonth6( 0 );

        $this->setSalesMonth7( 0 );
        $this->setMarginMonth7( 0 );

        $this->setSalesMonth8( 0 );
        $this->setMarginMonth8( 0 );

        $this->setSalesMonth9( 0 );
        $this->setMarginMonth9( 0 );

        $this->setSalesMonth10( 0 );
        $this->setMarginMonth10( 0 );

        $this->setSalesMonth11( 0 );
        $this->setMarginMonth11( 0 );

        $this->setSalesMonth12( 0 );
        $this->setMarginMonth12( 0 );

        $this->setYear( 0 );
        $this->user = new ArrayCollection();
    }

    public function __clone()
    {
        $this->targetId = null;
    }

    public function setTargetId($id)
    {
        $this->targetId = $id;

        return $this;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function setSalesMonth1( $value )
    {
        $this->salesMonth1 = $value;

        return $this;
    }

    public function getSalesMonth1()
    {
        return $this->salesMonth1;
    }

    public function setSalesMonth2( $value )
    {
        $this->salesMonth2 = $value;

        return $this;
    }

    public function getSalesMonth2()
    {
        return $this->salesMonth2;
    }

    public function setSalesMonth3( $value )
    {
        $this->salesMonth3 = $value;

        return $this;
    }

    public function getSalesMonth3()
    {
        return $this->salesMonth3;
    }

    public function setSalesMonth4( $value )
    {
        $this->salesMonth4 = $value;

        return $this;
    }

    public function getSalesMonth4()
    {
        return $this->salesMonth4;
    }

    public function setSalesMonth5( $value )
    {
        $this->salesMonth5 = $value;

        return $this;
    }

    public function getSalesMonth5()
    {
        return $this->salesMonth5;
    }

    public function setSalesMonth6( $value )
    {
        $this->salesMonth6 = $value;

        return $this;
    }

    public function getSalesMonth6()
    {
        return $this->salesMonth6;
    }

    public function setSalesMonth7( $value )
    {
        $this->salesMonth7 = $value;

        return $this;
    }

    public function getSalesMonth7()
    {
        return $this->salesMonth7;
    }

    public function setSalesMonth8( $value )
    {
        $this->salesMonth8 = $value;

        return $this;
    }

    public function getSalesMonth8()
    {
        return $this->salesMonth8;
    }

    public function setSalesMonth9( $value )
    {
        $this->salesMonth9 = $value;

        return $this;
    }

    public function getSalesMonth9()
    {
        return $this->salesMonth9;
    }

    public function setSalesMonth10( $value )
    {
        $this->salesMonth10 = $value;

        return $this;
    }

    public function getSalesMonth10()
    {
        return $this->salesMonth10;
    }

    public function setSalesMonth11( $value )
    {
        $this->salesMonth11 = $value;

        return $this;
    }

    public function getSalesMonth11()
    {
        return $this->salesMonth11;
    }

    public function setSalesMonth12( $value )
    {
        $this->salesMonth12 = $value;

        return $this;
    }

    public function getSalesMonth12()
    {
        return $this->salesMonth12;
    }

    public function setMarginMonth1( $value )
    {
        $this->marginMonth1 = $value;

        return $this;
    }

    public function getMarginMonth1()
    {
        return $this->marginMonth1;
    }


    public function setMarginMonth2( $value )
    {
        $this->marginMonth2 = $value;

        return $this;
    }

    public function getMarginMonth2()
    {
        return $this->marginMonth2;
    }


    public function setMarginMonth3( $value )
    {
        $this->marginMonth3 = $value;

        return $this;
    }

    public function getMarginMonth3()
    {
        return $this->marginMonth3;
    }


    public function setMarginMonth4( $value )
    {
        $this->marginMonth4 = $value;

        return $this;
    }

    public function getMarginMonth4()
    {
        return $this->marginMonth4;
    }


    public function setMarginMonth5( $value )
    {
        $this->marginMonth5 = $value;

        return $this;
    }

    public function getMarginMonth5()
    {
        return $this->marginMonth5;
    }


    public function setMarginMonth6( $value )
    {
        $this->marginMonth6 = $value;

        return $this;
    }

    public function getMarginMonth6()
    {
        return $this->marginMonth6;
    }


    public function setMarginMonth7( $value )
    {
        $this->marginMonth7 = $value;

        return $this;
    }

    public function getMarginMonth7()
    {
        return $this->marginMonth7;
    }


    public function setMarginMonth8( $value )
    {
        $this->marginMonth8 = $value;

        return $this;
    }

    public function getMarginMonth8()
    {
        return $this->marginMonth8;
    }


    public function setMarginMonth9( $value )
    {
        $this->marginMonth9 = $value;

        return $this;
    }

    public function getMarginMonth9()
    {
        return $this->marginMonth9;
    }


    public function setMarginMonth10( $value )
    {
        $this->marginMonth10 = $value;

        return $this;
    }

    public function getMarginMonth10()
    {
        return $this->marginMonth10;
    }


    public function setMarginMonth11( $value )
    {
        $this->marginMonth11 = $value;

        return $this;
    }

    public function getMarginMonth11()
    {
        return $this->marginMonth11;
    }


    public function setMarginMonth12( $value )
    {
        $this->marginMonth12 = $value;

        return $this;
    }

    public function getMarginMonth12()
    {
        return $this->marginMonth12;
    }

    public function setYear( $year )
    {
        $this->year = $year;

        return $this;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setUser( $user )
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars( $this );
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function exchangeArray( $data = array() )
    {
        $this->salesMonth1  = $data['salesMonth1'];
        $this->salesMonth2  = $data['salesMonth2'];
        $this->salesMonth3  = $data['salesMonth3'];
        $this->salesMonth4  = $data['salesMonth4'];
        $this->salesMonth5  = $data['salesMonth5'];
        $this->salesMonth6  = $data['salesMonth6'];
        $this->salesMonth7  = $data['salesMonth7'];
        $this->salesMonth8  = $data['salesMonth8'];
        $this->salesMonth9  = $data['salesMonth9'];
        $this->salesMonth10 = $data['salesMonth10'];
        $this->salesMonth11 = $data['salesMonth11'];
        $this->salesMonth12 = $data['salesMonth12'];

        $this->marginMonth1  = $data['marginMonth1'];
        $this->marginMonth2  = $data['marginMonth2'];
        $this->marginMonth3  = $data['marginMonth3'];
        $this->marginMonth4  = $data['marginMonth4'];
        $this->marginMonth5  = $data['marginMonth5'];
        $this->marginMonth6  = $data['marginMonth6'];
        $this->marginMonth7  = $data['marginMonth7'];
        $this->marginMonth8  = $data['marginMonth8'];
        $this->marginMonth9  = $data['marginMonth9'];
        $this->marginMonth10 = $data['marginMonth10'];
        $this->marginMonth11 = $data['marginMonth11'];
        $this->marginMonth12 = $data['marginMonth12'];

        $this->year = $data['year'];

    }

    protected $inputFilter;

    /**
     * set the input filter- only in forstructural and inheritance purposes
     *
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     *
     * @throws \Exception
     */
    public function setInputFilter( InputFilterInterface $inputFilter )
    {
        throw new \Exception( "Not used" );
    }


    /**
     *
     * @return Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        if ( !$this->inputFilter )
        {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            //example in google under search: "zf2 doctrine Album example"
            $inputFilter->add( $factory->createInput( array(
                'name'       => 'user', // 'usr_name'
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );

            $inputFilter->add( $factory->createInput( array(
                'name'       => 'year',
                'required'   => true,
                'filters'    => array(),
                'validators' => array(),
            ) ) );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}