<?php

use OJezu\DynamicParameterBundle\Kernel\Installation;
use Symfony\Component\HttpFoundation\Request;

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$request = Request::createFromGlobals();
$requestedInstallation = $request->server->get('APP_INSTALLATION'); // might be set by http server based on domain
$installation = new Installation($requestedInstallation);
$kernel = new AppKernel($installation, 'live', false); // application kernel extending OJezu\DynamicParameterBundle\Kernel\Kernel

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
