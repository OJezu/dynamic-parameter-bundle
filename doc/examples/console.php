#!/usr/bin/env php
<?php

use Symfony\Component\Console\Input\ArgvInput;
use OJezu\DynamicParameterBundle\Kernel\Installation;
use OJezu\DynamicParameterBundle\Kernel\Application;

$loader = require __DIR__.'/../app/autoload.php';

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');
$requestedInstallation = $input->getParameterOption(['--installation']);

$installation = new Installation($requestedInstallation, Installation::TYPE_PROD);
$kernel = new AppKernel($installation, $env);
$application = new Application($kernel);
$application->run($input);
