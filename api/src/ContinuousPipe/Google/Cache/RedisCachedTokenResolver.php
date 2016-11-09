<?php

namespace ContinuousPipe\Google\Cache;

use ContinuousPipe\Google\Token\Token;
use ContinuousPipe\Google\Token\TokenResolver;
use ContinuousPipe\Security\Account\GoogleAccount;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;

class RedisCachedTokenResolver implements TokenResolver
{
    /**
     * @var TokenResolver
     */
    private $decoratedResolver;

    /**
     * @var ClientInterface
     */
    private $redisClient;

    /**
     * The amount of seconds we remove from the token in order to ensure no token returned by the cache
     * will actually expire.
     *
     * @var int
     */
    private $expirationThreshold;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TokenResolver   $decoratedResolver
     * @param ClientInterface $redisClient
     * @param LoggerInterface $logger
     * @param int             $expirationThreshold
     */
    public function __construct(TokenResolver $decoratedResolver, ClientInterface $redisClient, LoggerInterface $logger, int $expirationThreshold)
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->redisClient = $redisClient;
        $this->logger = $logger;
        $this->expirationThreshold = $expirationThreshold;
    }

    /**
     * {@inheritdoc}
     */
    public function forAccount(GoogleAccount $account)
    {
        $accountUuid = $account->getUuid();
        $cacheKey = $accountUuid.'_google_token';

        if ($cachedValue = $this->redisClient->get($cacheKey)) {
            try {
                $token = $this->loadTokenFromCachedValue($cachedValue);
            } catch (\Throwable $e) {
                $this->logger->alert('Unable to load token from cached value: {message}', [
                    'message' => $e->getMessage(),
                    'exception' => $e,
                    'account' => $accountUuid,
                ]);
            }
        }

        if (!isset($token)) {
            $token = $this->decoratedResolver->forAccount($account);

            $this->storeToken($cacheKey, $token);
        }

        return $token;
    }

    /**
     * @param string $cachedValue
     *
     * @return Token
     */
    private function loadTokenFromCachedValue(string $cachedValue)
    {
        $json = \GuzzleHttp\json_decode($cachedValue);
        if (!array_key_exists('access_token', $json)) {
            throw new \InvalidArgumentException('The key `access_token` is not found in the loaded JSON');
        }

        $expirationDate = \DateTime::createFromFormat('U', $json['expiration_timestamp']);

        return new Token(
            $json['access_token'],
            $json['token_type'],
            $expirationDate,
            $json['id_token']
        );
    }

    /**
     * @param string $cacheKey
     * @param Token  $token
     */
    private function storeToken(string $cacheKey, Token $token)
    {
        $this->redisClient->setex($cacheKey, $token->getExpiresIn() - $this->expirationThreshold, \GuzzleHttp\json_encode([
            'expiration_timestamp' => time() + $token->getExpiresIn(),
            'access_token' => $token->getAccessToken(),
            'id_token' => $token->getIdToken(),
            'token_type' => $token->getTokenType(),
        ]));
    }
}
