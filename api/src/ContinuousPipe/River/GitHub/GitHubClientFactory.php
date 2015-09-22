<?php

namespace ContinuousPipe\River\GitHub;

use Github\Client;
use ContinuousPipe\User\User;
use Github\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GitHubClientFactory
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var HttpClientInterface
     */
    private $githubHttpClient;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, HttpClientInterface $githubHttpClient)
    {
        $this->tokenStorage = $tokenStorage;
        $this->githubHttpClient = $githubHttpClient;
    }

    /**
     * @param User $user
     *
     * @return Client
     *
     * @throws UserCredentialsNotFound
     */
    public function createClientForUser(User $user)
    {
        $client = new Client($this->githubHttpClient);

        if (null === ($userCredentials = $user->getGitHubCredentials())) {
            throw new UserCredentialsNotFound(sprintf(
                'No GitHub credentials found for user "%s"',
                $user->getEmail()
            ));
        }

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

    /**
     * @return Client
     */
    public function createAnonymous()
    {
        return new Client($this->githubHttpClient);
    }
}
