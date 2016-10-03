<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Github\Client;
use ContinuousPipe\Security\User\User;
use Github\HttpClient\HttpClientInterface;
use GitHub\Integration\Installation;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenResolver;
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
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @param TokenStorageInterface     $tokenStorage
     * @param HttpClientInterface       $githubHttpClient
     * @param BucketRepository          $bucketRepository
     * @param InstallationTokenResolver $installationTokenResolver
     * @param InstallationRepository    $installationRepository
     */
    public function __construct(TokenStorageInterface $tokenStorage, HttpClientInterface $githubHttpClient, BucketRepository $bucketRepository, InstallationTokenResolver $installationTokenResolver, InstallationRepository $installationRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->githubHttpClient = $githubHttpClient;
        $this->bucketRepository = $bucketRepository;
        $this->installationTokenResolver = $installationTokenResolver;
        $this->installationRepository = $installationRepository;
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

    /**
     * {@inheritdoc}
     */
    public function createClientForFlow(Flow $flow)
    {
        $repository = $flow->getRepository();

        // If the repository is not a GitHub code repository, creates the client
        // from the team bucket UUID
        if (!$repository instanceof GitHubCodeRepository) {
            return $this->createClientFromBucketUuid(
                $flow->getTeam()->getBucketUuid()
            );
        }

        try {
            $installation = $this->installationRepository->findByAccount(
                $repository->getGitHubRepository()->getOwner()->getLogin()
            );
        } catch (InstallationNotFound $e) {
            return $this->createClientFromBucketUuid(
                $flow->getTeam()->getBucketUuid()
            );
        }

        return $this->createClientFromInstallation($installation);
    }
}
