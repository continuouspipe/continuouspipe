<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Github\Client;
use ContinuousPipe\Security\User\User;
use Github\HttpClient\HttpClientInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GitHubClientFactory implements ClientFactory
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
     * {@inheritdoc}
     */
    public function createClientForUser(User $user)
    {
        return $this->createClientFromBucketUuid($user->getBucketUuid());
    }

    /**
     * {@inheritdoc}
     */
    public function createClientFromBucketUuid(Uuid $uuid)
    {
        try {
            $bucket = $this->bucketRepository->find($uuid);
        } catch (BucketNotFound $e) {
            throw new UserCredentialsNotFound($e->getMessage(), $e->getCode(), $e);
        }

        return $this->createClientFromBucket($bucket);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function createClientForCurrentUser()
    {
        $securityUser = $this->tokenStorage->getToken()->getUser();

        return $this->createClientForUser($securityUser->getUser());
    }
}
