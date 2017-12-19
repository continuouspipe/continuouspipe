<?php

namespace ContinuousPipe\Security\User;

use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\UserNotFound;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserContext
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get current user.
     *
     * @return \ContinuousPipe\Security\User\User
     *
     * @throws UserNotFound
     */
    public function getCurrent()
    {
        return $this->getCurrentSecurityUser()->getUser();
    }

    /**
     * Get current security user.
     *
     * @return SecurityUser
     *
     * @throws UserNotFound
     */
    private function getCurrentSecurityUser()
    {
        if (null === ($token = $this->tokenStorage->getToken())) {
            throw new UserNotFound('No token found in storage');
        }

        $securityUser = $token->getUser();
        if ($securityUser instanceof SecurityUser) {
            return $securityUser;
        }

        throw new UserNotFound('User found is not a security user');
    }
}
