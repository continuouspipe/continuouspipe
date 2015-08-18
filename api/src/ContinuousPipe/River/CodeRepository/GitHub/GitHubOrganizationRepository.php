<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\GitHub\GitHubClientFactory;
use Github\HttpClient\Message\ResponseMediator;
use Github\ResultPager;
use GitHub\WebHook\Model\Organization;
use GuzzleHttp\Exception\ClientException;
use JMS\Serializer\SerializerInterface;

class GitHubOrganizationRepository
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
        $found = $paginator->fetchAll($currentUserApi, 'organizations');
        $rawOrganizations = json_encode($found, true);

        $organizations = $this->serializer->deserialize(
            $rawOrganizations,
            'array<'.Organization::class.'>',
            'json'
        );

        return array_map(function (Organization $organization) {
            return new GitHubOrganization($organization);
        }, $organizations);
    }
}
