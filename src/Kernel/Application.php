<?php

namespace OJezu\DynamicParameterBundle\Kernel;

// not in composer.json, but if this class is used, these will be available
use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    /**
     * @inheritdoc
     */
    public function __construct(Kernel $kernel, $longOption = '--installation', $shortOption = null)
    {
        parent::__construct($kernel);

        $this->getDefinition()->addOption(
            new InputOption(
                $longOption,
                $shortOption,
                InputOption::VALUE_REQUIRED,
                'The installation name',
                $kernel->getInstallation()
            )
        );
    }
}
