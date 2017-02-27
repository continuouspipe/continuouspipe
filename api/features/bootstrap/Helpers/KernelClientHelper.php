<?php

namespace Helpers;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

trait KernelClientHelper
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var Response|null
     */
    private $response;

    protected function getResponse() : Response
    {
        return $this->response;
    }

    protected function request(Request $request)
    {
        $this->response = $this->kernel->handle($request);
    }

    protected function assertResponseCode(int $code)
    {
        if ($this->getResponse()->getStatusCode() != $code) {
            echo $this->getResponse()->getContent();

            throw new \RuntimeException(sprintf(
                'Expected code %d, but got %d',
                $code,
                $this->getResponse()->getStatusCode()
            ));
        }
    }

    protected function jsonResponse() : array
    {
        return \GuzzleHttp\json_decode($this->getResponse()->getContent(), true);
    }
}
