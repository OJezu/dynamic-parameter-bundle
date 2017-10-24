<?php

namespace OJezu\DynamicParameterBundle\Kernel;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Installation
{
    const TYPE_PROD = 'prod';
    const TYPE_NEXT = 'next';

    const TYPES = [
        self::TYPE_PROD,
        self::TYPE_NEXT,
    ];

    public function __construct($name, $type)
    {
        if (!in_array($type, self::TYPES)) {
            throw new InvalidConfigurationException('Unknown installation type: '.$type);
        }

        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;
}
