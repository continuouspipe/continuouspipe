<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use Github\HttpClient\Message\ResponseMediator;
use Github\ResultPager;
use GitHub\WebHook\Model\Repository;
use JMS\Serializer\SerializerInterface;

class GitHubCodeRepositoryRepository implements CodeRepositoryRepository
{
    /**
     * @var GitHubClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param GitHubClientFactory $gitHubClientFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory, SerializerInterface $serializer)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function findByCurrentUser()
    {
        $client = $this->gitHubClientFactory->createClientForCurrentUser();
        $currentUserApi = $client->currentUser();

        $paginator = new ResultPager($client);
        $found = $paginator->fetchAll($currentUserApi, 'repositories');
        $rawRepositories = json_encode($found, true);

        $repositories = $this->serializer->deserialize(
            $rawRepositories,
            'array<'.Repository::class.'>',
            'json'
        );

        return array_map(function (Repository $repository) {
            return new GitHubCodeRepository($repository);
        }, $repositories);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifier($id)
    {
        $response = $this->gitHubClientFactory->createClientForCurrentUser()->getHttpClient()->get(sprintf('/repositories/%d', $id));
        $foundRepository = ResponseMediator::getContent($response);
        $rawRepository = json_encode($foundRepository);

        $repository = $this->serializer->deserialize($rawRepository, Repository::class, 'json');

        return new GitHubCodeRepository($repository);
    }
}
