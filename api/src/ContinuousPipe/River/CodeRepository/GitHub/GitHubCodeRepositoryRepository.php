<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CodeRepositoryNotFound;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use ContinuousPipe\Security\User\User;
use Github\HttpClient\Message\ResponseMediator;
use Github\ResultPager;
use GitHub\WebHook\Model\Repository;
use GuzzleHttp\Exception\ClientException;
use JMS\Serializer\SerializerInterface;

class GitHubCodeRepositoryRepository implements CodeRepositoryRepository
{
    const LIMIT = 1000;

    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ClientFactory       $gitHubClientFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(ClientFactory $gitHubClientFactory, SerializerInterface $serializer)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        $client = $this->gitHubClientFactory->createClientForUser($user);

        $paginator = new ResultPager($client);
        $repositories = $paginator->fetch($client->user(), 'repositories', [$user->getUsername()]);

        while ($paginator->hasNext()) {
            $repositories = array_merge($repositories, $paginator->fetchNext());

            if (count($repositories) > self::LIMIT) {
                break;
            }
        }

        return $this->parseRepositories($repositories);
    }

    /**
     * {@inheritdoc}
     */
    public function findByOrganisation($organisation)
    {
        $client = $this->gitHubClientFactory->createClientForCurrentUser();
        $organisationApi = $client->organization();

        $paginator = new ResultPager($client);
        $found = $paginator->fetchAll($organisationApi, 'repositories', [$organisation]);

        return $this->parseRepositories($found);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifier($id)
    {
        $httpClient = $this->gitHubClientFactory->createClientForCurrentUser()->getHttpClient();

        try {
            $response = $httpClient->get(sprintf('/repositories/%d', $id));
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new CodeRepositoryNotFound(sprintf(
                    'Repository with identifier "%d" is not found',
                    $id
                ));
            }

            throw $e;
        }
        $foundRepository = ResponseMediator::getContent($response);
        $rawRepository = json_encode($foundRepository);

        $repository = $this->serializer->deserialize($rawRepository, Repository::class, 'json');

        return GitHubCodeRepository::fromRepository($repository);
    }

    /**
     * @param array $found
     *
     * @return CodeRepository[]
     */
    private function parseRepositories(array $found)
    {
        usort($found, function ($repo1, $repo2) {
            return strcasecmp($repo1['full_name'], $repo2['full_name']);
        });

        $rawRepositories = json_encode($found, true);

        $repositories = $this->serializer->deserialize(
            $rawRepositories,
            'array<'.Repository::class.'>',
            'json'
        );

        return array_map(function (Repository $repository) {
            return GitHubCodeRepository::fromRepository($repository);
        }, $repositories);
    }
}
