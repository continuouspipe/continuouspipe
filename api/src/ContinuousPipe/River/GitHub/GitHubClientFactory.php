<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\Security\Credentials\BucketRepository;
use Github\Client;
use ContinuousPipe\Security\User\User;
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
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param HttpClientInterface   $githubHttpClient
     * @param BucketRepository      $bucketRepository
     */
    public function __construct(TokenStorageInterface $tokenStorage, HttpClientInterface $githubHttpClient, BucketRepository $bucketRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->githubHttpClient = $githubHttpClient;
        $this->bucketRepository = $bucketRepository;
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
        $bucket = $this->bucketRepository->find($user->getBucketUuid());
        $gitHubTokens = $bucket->getGitHubTokens();

        if (0 === $gitHubTokens->count()) {
            throw new UserCredentialsNotFound(sprintf(
                'No GitHub credentials found for user "%s"',
                $user->getUsername()
            ));
        }

        $token = $gitHubTokens->first()->getAccessToken();
        $client->authenticate($token, null, Client::AUTH_HTTP_TOKEN);

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
