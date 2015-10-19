<?php

namespace ContinuousPipe\Authenticator\Security;

class InMemoryApiKeyRepository implements ApiKeyRepository
{
    /**
     * @var array
     */
    private $keys;

    /**
     * @param array $keys
     */
    public function __construct(array $keys = [])
    {
        $this->keys = $keys;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($apiKey)
    {
        return in_array($apiKey, $this->keys);
    }

    /**
     * Add an api key in the memory.
     *
     * @param string $apiKey
     */
    public function add($apiKey)
    {
        $this->keys[] = $apiKey;
    }
}
