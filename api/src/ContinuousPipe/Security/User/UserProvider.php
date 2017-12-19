<?php

namespace ContinuousPipe\Security\User;

use ContinuousPipe\Authenticator\Security\User\UserNotFound;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        try {
            $user = $this->userRepository->findOneByUsername($username);
        } catch (UserNotFound $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found', $username), $e->getCode(), $e);
        }

        return new SecurityUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        try {
            $user = $this->loadUserByUsername($user->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new UnsupportedUserException($e->getMessage(), $e->getCode(), $e);
        }

        return new SecurityUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === SecurityUser::class;
    }
}
