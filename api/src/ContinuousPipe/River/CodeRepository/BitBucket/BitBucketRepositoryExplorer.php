<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\CodeRepositoryExplorer;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\BitBucketAccount as SecurityBitBucketAccount;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class BitBucketRepositoryExplorer implements CodeRepositoryExplorer
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    public function __construct(ClientInterface $client, string $clientId, string $clientSecret)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserRepositories(Account $account): array
    {
        return $this->readRepositories('https://api.bitbucket.org/2.0/repositories/'.$account->getUsername(), [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getAuthenticationToken($account),
            ],
        ]);
    }

    /**
     * @param string $url
     * @param array $options
     *
     * @return BitBucketCodeRepository[]
     */
    private function readRepositories(string $url, array $options)
    {
        $response = $this->client->request('GET', $url, $options);
        $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        $values = $json['values'];

        $repositories = $this->parseRepositories($values);

        if (isset($json['next'])) {
            $repositories = array_merge($repositories, $this->readRepositories($json['next'], $options));
        }

        return $repositories;
    }

    /**
     * {@inheritdoc}
     */
    public function findOrganisations(Account $account): array
    {
        $response = $this->client->request('GET', 'https://api.bitbucket.org/2.0/teams', [
            'query' => [
                'role' => 'contributor',
            ],
            'headers' => [
                'Authorization' => 'Bearer '.$this->getAuthenticationToken($account),
            ],
        ]);

        $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return array_map(function (array $organisation) {
            return new BitBucketOrganisation(
                $organisation['username'],
                $organisation['links']['avatar']['href']
            );
        }, $json['values']);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrganisationRepositories(Account $account, string $organisationIdentifier): array
    {
        return $this->readRepositories('https://api.bitbucket.org/2.0/teams/'.$organisationIdentifier.'/repositories', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getAuthenticationToken($account),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Account $account): bool
    {
        return $account instanceof SecurityBitBucketAccount;
    }

    /**
     * @param SecurityBitBucketAccount $account
     *
     * @throws CodeRepositoryException
     *
     * @return string
     */
    private function getAuthenticationToken(SecurityBitBucketAccount $account) : string
    {
        try {
            $response = $this->client->request('POST', 'https://bitbucket.org/site/oauth2/access_token', [
                'auth' => [$this->clientId, $this->clientSecret],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $account->getRefreshToken(),
                ],
            ]);
        } catch (RequestException $e) {
            throw new CodeRepositoryException('Unable to get the BitBucket token', $e->getCode(), $e);
        }

        try {
            $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (\InvalidArgumentException $e) {
            throw new CodeRepositoryException('Invalid JSON response from BitBucket', $e->getCode(), $e);
        }

        return $json['access_token'];
    }

    /**
     * @param array $values
     *
     * @return array
     */
    private function parseRepositories(array $values): array
    {
        return array_map(function (array $repository) {
            return new BitBucketCodeRepository(
                $repository['uuid'],
                new BitBucketAccount(
                    $repository['owner']['uuid'],
                    $repository['owner']['username'],
                    $repository['owner']['type'],
                    $repository['owner']['display_name']
                ),
                $repository['name'],
                $repository['links']['self']['href'],
                'master',
                $repository['is_private']
            );
        }, $values);
    }
}
