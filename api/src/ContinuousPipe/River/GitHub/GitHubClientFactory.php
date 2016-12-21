<?php

namespace ContinuousPipe\River\GitHub;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\GitHubAccount;
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
use Ramsey\Uuid\UuidInterface;
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
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @param TokenStorageInterface     $tokenStorage
     * @param HttpClientInterface       $githubHttpClient
     * @param BucketRepository          $bucketRepository
     * @param InstallationRepository    $installationRepository
     * @param InstallationClientFactory $installationClientFactory
     * @param FlatFlowRepository        $flatFlowRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        HttpClientInterface $githubHttpClient,
        BucketRepository $bucketRepository,
        InstallationRepository $installationRepository,
        InstallationClientFactory $installationClientFactory,
        FlatFlowRepository $flatFlowRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->githubHttpClient = $githubHttpClient;
        $this->bucketRepository = $bucketRepository;
        $this->installationRepository = $installationRepository;
        $this->installationClientFactory = $installationClientFactory;
        $this->flatFlowRepository = $flatFlowRepository;
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
        $gitHubTokens = $credentialsBucket->getGitHubTokens();

        if (0 === $gitHubTokens->count()) {
            throw new UserCredentialsNotFound(sprintf(
                'No GitHub credentials found in bucket "%s"',
                $credentialsBucket->getUuid()
            ));
        }

        return $this->createClientFromToken(
            $gitHubTokens->first()->getAccessToken()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createClientFromAccount(GitHubAccount $account)
    {
        return $this->createClientFromToken(
            $account->getToken()
        );
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
    public function createClientForFlow(UuidInterface $flowUuid)
    {
        $flow = $this->flatFlowRepository->find($flowUuid);
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

    /**
     * @param string $token
     *
     * @return Client
     */
    private function createClientFromToken(string $token): Client
    {
        $client = new Client($this->githubHttpClient);
        $client->authenticate($token, null, Client::AUTH_HTTP_TOKEN);

        return $client;
    }
}
