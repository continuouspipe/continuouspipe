<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\GitHub\GitHubClientFactory;
use Github\HttpClient\Message\ResponseMediator;
use Github\ResultPager;
use GitHub\WebHook\Model\Organisation;
use GuzzleHttp\Exception\ClientException;
use JMS\Serializer\SerializerInterface;

class GitHubOrganisationRepository
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
        $rawOrganisations = json_encode($found, true);

        $organisations = $this->serializer->deserialize(
            $rawOrganisations,
            'array<'.Organisation::class.'>',
            'json'
        );

        return array_map(function (Organisation $organisation) {
            return new GitHubOrganisation($organisation);
        }, $organisations);
    }
}
