<?php

namespace GuzzleHttp;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

class PredefinedRequestMappingMiddleware
{
    /**
     * This array contains different mappgins.
     *
     * An example would be:
     * [
     *     [
     *         'method' => 'GET',
     *         'url' => '/\/projects$/',
     *         'response' => $responseObjectOrException
     *     ]
     * ]
     *
     * @var array
     */
    private $mappings = [];

    public function __invoke()
    {
        return function (RequestInterface $request, array $options) {
            foreach ($this->mappings as $mapping) {
                if (!$this->matches($mapping, $request)) {
                    continue;
                }

                $response = $mapping['response'];

                return $response instanceof \Exception
                    ? new RejectedPromise($response)
                    : \GuzzleHttp\Promise\promise_for($response);
            }

            return new RejectedPromise(new RequestException(
                sprintf('%s %s: Not handled by the request mapping', $request->getMethod(), $request->getUri()),
                $request
            ));
        };
    }

    /**
     * @param array $mapping
     */
    public function addMapping(array $mapping)
    {
        $this->mappings[] = $mapping;
    }

    /**
     * @param array $mapping
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function matches(array $mapping, RequestInterface $request)
    {
        if (array_key_exists('method', $mapping) && $mapping['method'] != $request->getMethod()) {
            return false;
        }

        return 1 === preg_match($mapping['path'], $request->getUri()->getPath());
    }
}
