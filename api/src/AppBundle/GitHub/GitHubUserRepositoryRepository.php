<?php

namespace AppBundle\GitHub;

use AppBundle\Repository\UserRepositoryRepository;
use Github\HttpClient\Message\ResponseMediator;
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
        $foundRepositories = $this->gitHubClientFactory->createClientForCurrentUser()->currentUser()->repositories();
        $rawRepositories = json_encode($foundRepositories);

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
