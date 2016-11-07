<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Security\Account\GoogleAccount;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\PredefinedRequestMappingMiddleware;

class PredictableClientFactory implements ClientFactory
{
    /**
     * @var PredefinedRequestMappingMiddleware
     */
    private $predefinedRequestMappingMiddleware;

    /**
     * @param PredefinedRequestMappingMiddleware $predefinedRequestMappingMiddleware
     */
    public function __construct(PredefinedRequestMappingMiddleware $predefinedRequestMappingMiddleware)
    {
        $this->predefinedRequestMappingMiddleware = $predefinedRequestMappingMiddleware;
    }

    /**
     * {@inheritdoc}
     */
    function fromAccount(GoogleAccount $account)
    {
        $handler = HandlerStack::create();
        $handler->unshift($this->predefinedRequestMappingMiddleware);

        return new Client([
            'handler' => $handler,
        ]);
    }
}
