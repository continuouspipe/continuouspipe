<?php

namespace ContinuousPipe\Authenticator\Security\ApiKey;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserByApiKeyRepository
{
    /**
     * @param string $key
     *
     * @return UserInterface|null
     */
    public function findUserByApiKey(string $key);
}
