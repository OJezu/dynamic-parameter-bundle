<?php

namespace OJezu\DynamicParameterBundle\Service;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class DynamicParameterEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @var ParameterProviderInterface
     */
    private $parameterProvider;

    /**
     * @var array[]
     */
    private $parameterMap;

    /**
     * @var string[]
     */
    private $cache;

    /**
     * @var bool
     */
    private $noConfigurationMode;

    /**
     * @param ParameterProviderInterface $parameterProvider
     * @param array $parameterMap
     * @param bool $loadConfiguration
     */
    public function __construct(ParameterProviderInterface $parameterProvider, array $parameterMap, $loadConfiguration = true)
    {
        $this->parameterProvider = $parameterProvider;
        $this->parameterMap = $parameterMap;
        $this->cache = [];
        $this->noConfigurationMode = !$loadConfiguration;
    }

    /**
     * @param $prefix
     * @param $name
     * @param \Closure $getEnv
     *
     * @return mixed
     */
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        if (!array_key_exists($name, $this->cache)) {
            if (!array_key_exists($name, $this->parameterMap)) {
                throw new InvalidConfigurationException(sprintf(
                    'OJezu\\DynamicParameterBundle cannot map parameter »%s« as it is not found in parameter map',
                    $name
                ));
            }

            if ($this->noConfigurationMode) {
                $this->cache[$name] = array_key_exists('no_config_value', $this->parameterMap[$name])
                    ? $this->parameterMap[$name]['no_config_value']
                    : null;
            } elseif ($this->parameterProvider->has($this->parameterMap[$name]['path'])) {
                $this->cache[$name] = $this->parameterProvider->get($this->parameterMap[$name]['path']);
            } elseif (array_key_exists('default', $this->parameterMap[$name])) {
                $this->cache[$name] = $this->parameterMap[$name]['default'];
            } else {
                throw new InvalidConfigurationException(sprintf(
                    'Path »%s« for parameter »%s« was not found',
                    implode('.', $this->parameterMap[$name]['path']),
                    $name
                ));
            }
        }

        return $this->cache[$name];
    }

    /**
     * @return string[] The PHP-types managed by getEnv(), keyed by prefixes
     */
    public static function getProvidedTypes()
    {
        return ['ojezu_param' => 'bool|int|float|string|array'];
    }
}
