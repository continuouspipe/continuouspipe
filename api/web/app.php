<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

$kernel = new AppKernel('prod', false);

// Trust the remote proxy
Request::setTrustedProxies(array('127.0.0.1', $request->server->get('REMOTE_ADDR')));

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
