<?php

namespace OJezu\DynamicParameterBundle\Kernel;

class Installation
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}
