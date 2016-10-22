<?php
namespace Resource\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CostCodeFilter extends InputFilter
{
    public function __construct()
    {
        // self::__construct(); // parent::__construct(); - trows and error
        $this->add( array(
            'name'     => 'name',
            'required' => false,
        ) );
    }
}