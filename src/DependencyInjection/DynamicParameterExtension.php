<?php

namespace OJezu\DynamicParameterBundle\DependencyInjection;

use OJezu\DynamicParameterBundle\Service\DynamicParameterEnvVarProcessor;
use OJezu\DynamicParameterBundle\Service\InstallationEnvVarProcessor;
use OJezu\DynamicParameterBundle\Service\JsonFileParameterProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * @inheritdoc
 */

class DynamicParameterExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $builder
     */
    public function load(array $configs, ContainerBuilder $builder)
    {
        $processed = $this->processConfiguration(new Configuration(), $configs);

        $this->configureParameters($processed, $builder);
        $this->configureMultiInstallation($processed, $builder);
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return 'ojezu_dynamic_parameter';
    }

    /**
     * @param array $processed
     * @param ContainerBuilder $builder
     */
    private function configureParameters(array $processed, ContainerBuilder $builder)
    {
        if (array_key_exists('advanced_parameters', $processed)) {
            $paramConfiguration = $processed['advanced_parameters'];
            $providerReference = null;

            if ($paramConfiguration['json_provider']) {
                $providerDefinition = new Definition(
                    JsonFileParameterProvider::class,
                    [$paramConfiguration['json_provider']['file_path']]
                );
                $providerDefinition->setPublic(false);
                $builder->addDefinitions([JsonFileParameterProvider::class => $providerDefinition]);

                $providerReference = new Reference(JsonFileParameterProvider::class);
            } elseif ($paramConfiguration['processor']['provider']['service']) {
                $providerReference = new Reference($paramConfiguration['processor']['provider']['service']);
            }

            if (!$providerReference) {
                throw new InvalidConfigurationException(
                    'OJezu\DynamicParameterBundle: Either a provider must be configured or provider service must be specified.'
                );
            }

            $processorDefinition = new Definition(DynamicParameterEnvVarProcessor::class, [
                $providerReference,
                $paramConfiguration['processor']['parameter_map'],
                $paramConfiguration['processor']['load_configuration']
            ]);

            $processorDefinition->addTag('container.env_var_processor');
            $processorDefinition->setPublic(false);

            $builder->addDefinitions([DynamicParameterEnvVarProcessor::class => $processorDefinition]);
        }
    }

    /**
     * @param array $processed
     * @param ContainerBuilder $builder
     */
    private function configureMultiInstallation(array $processed, ContainerBuilder $builder)
    {
        if ($processed['multi_installation']) {
            $installationEnvProcessorDef = new Definition(InstallationEnvVarProcessor::class, [
                new Expression('service("kernel").getInstallation()'),
            ]);
            $installationEnvProcessorDef->addTag('container.env_var_processor');
            $builder->addDefinitions([
                InstallationEnvVarProcessor::class => $installationEnvProcessorDef,
            ]);
        }
    }
}
