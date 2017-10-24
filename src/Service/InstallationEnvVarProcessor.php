<?php

namespace OJezu\DynamicParameterBundle\Service;

use OJezu\DynamicParameterBundle\Kernel\Installation;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class InstallationEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @var Installation
     */
    private $installation;

    /**
     * @param Installation $installation
     */
    public function __construct(Installation $installation)
    {
        $this->installation = $installation;
    }

    /**
     * Returns the value of the given variable as managed by the current instance.
     *
     * @param string $prefix The namespace of the variable
     * @param string $name The name of the variable within the namespace
     * @param \Closure $getEnv A closure that allows fetching more env vars
     *
     * @return mixed
     *
     * @throws RuntimeException on error
     */
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        if (!property_exists($this->installation, $name)) {
            throw new InvalidArgumentException(sprintf('Installation object does not have »%s« property', $name));
        }

        return $this->installation->$name;
    }

    /**
     * @return string[] The PHP-types managed by getEnv(), keyed by prefixes
     */
    public static function getProvidedTypes()
    {
        return ['ojezu_installation' => 'string'];
    }
}
