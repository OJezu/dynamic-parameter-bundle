<?php

namespace OJezu\DynamicParameterBundle;

use OJezu\DynamicParameterBundle\DependencyInjection\DynamicParameterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DynamicParameterBundle extends Bundle
{
    public function build(ContainerBuilder $containerBuilder)
    {
        $this->extension = new DynamicParameterExtension();
    }
}
