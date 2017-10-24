<?php

namespace OJezu\DynamicParameterBundle\Kernel;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * NOTE: I've tried overwriting initializeContainer and adding 'installation' as service there, to avoid injecting
 * entire kernel (or depending on entire kernel) in services, but that caused generating cache to have a success rate of
 * 50%
 */
abstract class Kernel extends BaseKernel
{
    /**
     * @var Installation
     */
    private $installation;

    /**
     * @param Installation $installation
     * @param bool $environment
     * @param $debug
     */
    public function __construct(Installation $installation, $environment, $debug)
    {
        parent::__construct($environment, $debug);
        $this->installation = $installation;
    }

    /**
     * @return Installation
     */
    public function getInstallation()
    {
        return $this->installation;
    }
}
