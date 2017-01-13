<?php
declare(strict_types=1);

namespace ContinuousPipe\Adapter\Kubernetes\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class FaultToleranceConfigurator
{
    public function configureToBeFaultTolerant(Client $client)
    {
        $handlerStack = $client->getConfig('handler');
        $handlerStack->push($this->getRetryMiddleware());
    }

    private function getRetryMiddleware() : callable
    {
        return Middleware::retry(
            $this->getRetryDecider(),
            $this->getRetryDelayCalculator()
        );
    }

    /**
     * Tell Guzzle to do up to 5 retries
     */
    private function getRetryDecider() : callable
    {
        $maxRetries = 5;

        return
            function (
                int $retries,
                RequestInterface $request,
                ResponseInterface $response = null,
                RequestException $exception = null
            ) use ($maxRetries) : bool
            {
                if ($retries >= $maxRetries) {
                    return false;
                }
                return null == $response || $response->getStatusCode() >= 500;
            };
    }

    /**
     * Tell Guzzle to wait half a second longer each time
     */
    private function getRetryDelayCalculator() : callable
    {
        return function( $numberOfRetries ) {
            return $numberOfRetries * 500;
        };
    }
}
