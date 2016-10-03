<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Github\Client;
use ContinuousPipe\Security\User\User;
use Github\HttpClient\HttpClientInterface;
use GitHub\Integration\Installation;
use GitHub\Integration\InstallationTokenResolver;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
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
     * @var InstallationTokenResolver
     */
    private $installationTokenResolver;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param HttpClientInterface $githubHttpClient
     * @param BucketRepository $bucketRepository
     * @param InstallationTokenResolver $installationTokenResolver
     */
    public function __construct(TokenStorageInterface $tokenStorage, HttpClientInterface $githubHttpClient, BucketRepository $bucketRepository, InstallationTokenResolver $installationTokenResolver)
    {
        $this->tokenStorage = $tokenStorage;
        $this->githubHttpClient = $githubHttpClient;
        $this->bucketRepository = $bucketRepository;
        $this->installationTokenResolver = $installationTokenResolver;
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

    /**
     * {@inheritdoc}
     */
    public function createClientFromInstallation(Installation $installation)
    {
        $token = $this->installationTokenResolver->get($installation);

        $client = new Client($this->githubHttpClient);
        $client->authenticate($token->getToken(), null, Client::AUTH_HTTP_TOKEN);
        $client->setHeaders([
            'Accept' => 'application/vnd.github.machine-man-preview+json',
        ]);

        return $client;
    }
}
