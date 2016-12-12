<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Github\Client;
use ContinuousPipe\Security\User\User;
use Github\HttpClient\HttpClientInterface;
use GitHub\Integration\Installation;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
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
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @var InstallationClientFactory
     */
    private $installationClientFactory;

    /**
     * @param TokenStorageInterface     $tokenStorage
     * @param HttpClientInterface       $githubHttpClient
     * @param BucketRepository          $bucketRepository
     * @param InstallationRepository    $installationRepository
     * @param InstallationClientFactory $installationClientFactory
     */
    public function __construct(TokenStorageInterface $tokenStorage, HttpClientInterface $githubHttpClient, BucketRepository $bucketRepository, InstallationRepository $installationRepository, InstallationClientFactory $installationClientFactory)
    {
        $this->tokenStorage = $tokenStorage;
        $this->githubHttpClient = $githubHttpClient;
        $this->bucketRepository = $bucketRepository;
        $this->installationRepository = $installationRepository;
        $this->installationClientFactory = $installationClientFactory;
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
    public function createClientFromInstallation(Installation $installation)
    {
        return $this->installationClientFactory->createClientFromInstallation($installation);
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
    public function createClientForFlow(FlatFlow $flow)
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
            $installation = $this->installationRepository->findByRepository($repository);
        } catch (InstallationNotFound $e) {
            return $this->createClientFromBucketUuid(
                $flow->getTeam()->getBucketUuid()
            );
        }

        return $this->createClientFromInstallation($installation);
    }
}
