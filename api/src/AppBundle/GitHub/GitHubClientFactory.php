<?php

namespace AppBundle\GitHub;

use Github\Client;
use Kubernetes\Manager\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GitHubClientFactory
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
     * @param User $user
     *
     * @return Client
     */
    public function createClientForUser(User $user)
    {
        $client = new Client();

        $userCredentials = $user->getGitHubCredentials();
        $client->authenticate($userCredentials->getAccessToken(), null, Client::AUTH_HTTP_TOKEN);

        return $client;
    }

    /**
     * @return Client
     */
    public function createClientForCurrentUser()
    {
        $securityUser = $this->tokenStorage->getToken()->getUser();

        return $this->createClientForUser($securityUser->getUser());
    }
}
