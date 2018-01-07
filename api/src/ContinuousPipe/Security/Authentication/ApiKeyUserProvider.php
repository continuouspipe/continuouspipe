<?php

namespace ContinuousPipe\Security\Authentication;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface ApiKeyUserProvider extends UserProviderInterface
{
    /**
     * @param string $apiKey
     *
     * @throws AuthenticationException
     *
     * @return UserInterface
     */
    public function getUserForApiKey(string $apiKey);
}
