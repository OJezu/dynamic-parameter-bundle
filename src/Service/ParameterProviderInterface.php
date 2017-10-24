<?php

namespace OJezu\DynamicParameterBundle\Service;

interface ParameterProviderInterface
{
    /**
     * @param array $parameterPath
     *
     * @return string
     */
    public function get(array $parameterPath);

    /**
     * @param array $parameterPath
     *
     * @return bool
     */
    public function has(array $parameterPath);
}
