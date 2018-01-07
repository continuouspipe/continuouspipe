<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\Token\TokenResolver;
use ContinuousPipe\Security\Account\GoogleAccount;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class TokenClientFactory implements ClientFactory
{
    /**
     * @var TokenResolver
     */
    private $tokenResolver;

    /**
     * @param TokenResolver $tokenResolver
     */
    public function __construct(TokenResolver $tokenResolver)
    {
        $this->tokenResolver = $tokenResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function fromAccount(GoogleAccount $account)
    {
        $handler = HandlerStack::create();
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) use ($account) {
            $token = $this->tokenResolver->forAccount($account);

            return $request->withHeader('Authorization', 'Bearer '.$token->getAccessToken());
        }));

        return new Client([
            'handler' => $handler,
        ]);
    }
}
