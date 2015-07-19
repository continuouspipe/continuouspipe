<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use AppBundle\Repository\UserRepositoryRepository;
use Github\HttpClient\Message\ResponseMediator;
use Github\ResultPager;
use GitHub\WebHook\Model\Repository;
use JMS\Serializer\SerializerInterface;
use ContinuousPipe\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GitHubUserRepositoryRepository implements UserRepositoryRepository
{
    /**
     * @var GitHubClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

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

        return $repositories;
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $response = $this->gitHubClientFactory->createClientForCurrentUser()->getHttpClient()->get(sprintf('/repositories/%d', $id));
        $foundRepository = ResponseMediator::getContent($response);
        $rawRepository = json_encode($foundRepository);

        $repository = $this->serializer->deserialize($rawRepository, Repository::class, 'json');

        return $repository;
    }
}
