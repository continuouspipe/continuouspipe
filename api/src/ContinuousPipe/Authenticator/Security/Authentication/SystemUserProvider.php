<?php

namespace ContinuousPipe\Authenticator\Security\Authentication;

use ContinuousPipe\Authenticator\Security\ApiKeyRepository;
use ContinuousPipe\Authenticator\Security\User\SystemUser;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SystemUserProvider implements UserProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $decorated;

    /**
     * @var ApiKeyRepository
     */
    private $apiKeyRepository;

    /**
     * @param UserProviderInterface $decorated
     * @param ApiKeyRepository      $apiKeyRepository
     */
    public function __construct(UserProviderInterface $decorated, ApiKeyRepository $apiKeyRepository)
    {
        $this->decorated = $decorated;
        $this->apiKeyRepository = $apiKeyRepository;
    }

    /**
     * @param string $apiKey
     *
     * @throws AuthenticationException
     *
     * @return SystemUser
     */
    public function getUserForApiKey($apiKey)
    {
        if (!$this->apiKeyRepository->exists($apiKey)) {
            throw new AuthenticationException(sprintf(
                'API key "%s" do not exists',
                $apiKey
            ));
        }

        return new SystemUser($apiKey);
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
