<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\Security\Credentials\Bucket;
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
        $bucket = $this->bucketRepository->find($user->getBucketUuid());

        return $this->createClientFromBucket($bucket);
    }

    /**
     * @param Bucket $credentialsBucket
     * @return Client
     * @throws UserCredentialsNotFound
     */
    public function createClientFromBucket(Bucket $credentialsBucket)
    {
        $client = new Client($this->githubHttpClient);
        $gitHubTokens = $credentialsBucket->getGitHubTokens();

        if (0 === $gitHubTokens->count()) {
            throw new UserCredentialsNotFound(sprintf(
                'No GitHub credentials found in bucket "%s"',
                $credentialsBucket->getUuid()
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
