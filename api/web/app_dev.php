<?php

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
$allowedIpRanges = ['127.0.0.1', '::1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'];
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(IpUtils::checkIp(@$_SERVER['REMOTE_ADDR'], $allowedIpRanges) || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

// Validate timestamps for the dev environment
ini_set('opcache.validate_timestamps', 1);

Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();

// Trust the remote proxy
Request::setTrustedProxies(array('127.0.0.1', $request->server->get('REMOTE_ADDR')));

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
