<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\OrganisationRepository;
use ContinuousPipe\River\GitHub\ClientFactory;
use Github\ResultPager;
use GitHub\WebHook\Model\Organisation;
use JMS\Serializer\SerializerInterface;

class GitHubOrganisationRepository implements OrganisationRepository
{
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
            return GitHubOrganisation::fromGitHubOrganisation($organisation);
        }, $organisations);
    }
}
