<?php

use OJezu\DynamicParameterBundle\Kernel\Installation;
use Symfony\Component\HttpFoundation\Request;

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$request = Request::createFromGlobals();
$requestedInstallation = $request->server->get('SYMFONY_INSTALLATION'); // might be set by http server based on domain
$installation = new Installation($requestedInstallation);
$kernel = new AppKernel($installation, 'live', false);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
