<?php

namespace ContinuousPipe\Google\Cache;

use ContinuousPipe\Google\Token\Token;
use ContinuousPipe\Google\Token\TokenResolver;
use ContinuousPipe\Security\Account\GoogleAccount;

class InMemoryCachedTokenResolver implements TokenResolver
{
    /**
     * @var TokenResolver
     */
    private $decoratedResolver;

    /**
     * @var Token[]
     */
    private $cachedTokens = [];

    /**
     * @param TokenResolver $decoratedResolver
     */
    public function __construct(TokenResolver $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function forAccount(GoogleAccount $account)
    {
        $key = $account->getUuid();
        if (!array_key_exists($key, $this->cachedTokens)) {
            $this->cachedTokens[$key] = $this->decoratedResolver->forAccount($account);
        }

        return $this->cachedTokens[$key];
    }
}
