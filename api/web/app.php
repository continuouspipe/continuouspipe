<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

$kernel = new AppKernel('prod', false);

$request = Request::createFromGlobals();
Request::setTrustedProxies(array('127.0.0.1', $request->server->get('REMOTE_ADDR')));

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
