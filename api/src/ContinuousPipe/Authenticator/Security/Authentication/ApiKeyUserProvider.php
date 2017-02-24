<?php

namespace ContinuousPipe\Authenticator\Security\Authentication;

use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKeyRepository;
use ContinuousPipe\Authenticator\Security\User\SystemUser;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $decorated;

    /**
     * @var UserApiKeyRepository
     */
    private $apiKeyUserRepository;

    /**
     * @param UserProviderInterface  $decorated
     * @param UserApiKeyRepository $apiKeyUserRepository
     */
    public function __construct(UserProviderInterface $decorated, UserApiKeyRepository $apiKeyUserRepository)
    {
        $this->decorated = $decorated;
        $this->apiKeyUserRepository = $apiKeyUserRepository;
    }

    /**
     * @param string $apiKey
     *
     * @throws AuthenticationException
     *
     * @return UserInterface
     */
    public function getUserForApiKey($apiKey)
    {
        if (null === ($user = $this->apiKeyUserRepository->findUserByApiKey($apiKey))) {
            throw new AuthenticationException(sprintf(
                'API key "%s" do not exists',
                $apiKey
            ));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->decorated->loadUserByUsername($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->decorated->refreshUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return SystemUser::class === $class || $this->decorated->supportsClass($class);
    }
}
