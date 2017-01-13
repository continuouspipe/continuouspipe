<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\CodeRepositoryExplorer;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\GitHubClientException;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\GitHubAccount;
use Github\Api\ApiInterface;
use Github\Client;
use Github\ResultPager;
use GitHub\WebHook\Model\Repository;
use GuzzleHttp\Exception\ClientException;
use JMS\Serializer\SerializerInterface;
use GitHub\WebHook\Model\Organisation as GitHubModelOrganisation;

class GitHubRepositoryExplorer implements CodeRepositoryExplorer
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
    public function findUserRepositories(Account $account): array
    {
        $client = $this->getGitHubClientFromAccount($account);

        return $this->parseRepositories(
            $this->fetchAll(
                $client,
                $client->currentUser(),
                'repositories',
                [
                    'owner',
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findOrganisations(Account $account): array
    {
        $client = $this->getGitHubClientFromAccount($account);

        $found = $this->fetchAll(
            $client,
            $client->currentUser(),
            'organizations'
        );

        $rawOrganisations = json_encode($found, true);

        $organisations = $this->serializer->deserialize(
            $rawOrganisations,
            'array<'.GitHubModelOrganisation::class.'>',
            'json'
        );

        return array_map(function (GitHubModelOrganisation $organisation) {
            return GitHubOrganisation::fromGitHubOrganisation($organisation);
        }, $organisations);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrganisationRepositories(Account $account, string $organisationIdentifier): array
    {
        $client = $this->getGitHubClientFromAccount($account);

        return $this->parseRepositories(
            $this->fetchAll(
                $client,
                $client->organization(),
                'repositories',
                [
                    $organisationIdentifier,
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Account $account): bool
    {
        return $account instanceof GitHubAccount;
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

    private function fetchAll(Client $client, ApiInterface $api, string $method, array $parameters = []) : array
    {
        $paginator = new ResultPager($client);

        try {
            $repositories = $paginator->fetch($api, $method, $parameters);

            while ($paginator->hasNext()) {
                $repositories = array_merge($repositories, $paginator->fetchNext());

                if (count($repositories) > self::LIMIT) {
                    break;
                }
            }
        } catch (ClientException $e) {
            $message = 'Unable to list the code repositories';
            if (null !== ($response = $e->getResponse())) {
                if ($response->getStatusCode() == 401) {
                    $message .= ', GitHub un-authorized the request. Could you unlink and link your GitHub account again? '.
                                '(Click on the "Account" dropdown, on the top-right)';
                }
            }

            throw new CodeRepositoryException($message, $e->getCode(), $e);
        }

        return $repositories;
    }

    /**
     * @param Account $account
     *
     * @throws CodeRepositoryException
     *
     * @return Client
     */
    private function getGitHubClientFromAccount(Account $account): Client
    {
        try {
            return $this->gitHubClientFactory->createClientFromAccount($account);
        } catch (GitHubClientException $e) {
            throw new CodeRepositoryException('Unable to create the GitHub client', $e->getCode(), $e);
        }
    }
}
