<?php

namespace ContinuousPipe\Authenticator\Security;

interface ApiKeyRepository
{
    /**
     * Checks if the API key exists.
     *
     * @param string $apiKey
     *
     * @return bool
     */
    public function exists($apiKey);
}
