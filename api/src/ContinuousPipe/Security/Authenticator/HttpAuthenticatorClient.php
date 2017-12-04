<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountNotFound;
use ContinuousPipe\Security\ApiKey\UserApiKey;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\Cluster;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamUsageLimits;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class HttpAuthenticatorClient implements AuthenticatorClient
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $authenticatorUrl;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $authenticationToken;

    /**
     * @param Client              $httpClient
     * @param SerializerInterface $serializer
     * @param string              $authenticatorUrl
     * @param string              $authenticationToken
     */
    public function __construct(Client $httpClient, SerializerInterface $serializer, $authenticatorUrl, $authenticationToken)
    {
        $this->httpClient = $httpClient;
        $this->authenticatorUrl = $authenticatorUrl;
        $this->serializer = $serializer;
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserByUsername($username)
    {
        $url = $this->authenticatorUrl.'/api/user/'.urlencode($username);

        try {
            $response = $this->get($url);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new UserNotFound(sprintf('User "%s" is not found', $username));
            }

            throw $e;
        }

        $user = $this->serializer->deserialize($response->getBody()->getContents(), User::class, 'json');

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function findBucketByUuid(UuidInterface $uuid)
    {
        $url = $this->authenticatorUrl.'/api/bucket/'.((string) $uuid);

        try {
            $response = $this->get($url);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new BucketNotFound(sprintf('Bucket "%s" is not found', $uuid));
            }

            throw $e;
        }

        $bucket = $this->serializer->deserialize($response->getBody()->getContents(), Bucket::class, 'json');

        return $bucket;
    }

    /**
     * {@inheritdoc}
     */
    public function findTeamBySlug($slug)
    {
        $url = $this->authenticatorUrl.'/api/teams/'.$slug;

        try {
            $response = $this->get($url);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw TeamNotFound::createFromSlug($slug);
            }

            throw $e;
        }

        $team = $this->serializer->deserialize($response->getBody()->getContents(), Team::class, 'json');

        return $team;
    }


    public function findTeamUsageLimitsBySlug(string $slug) : TeamUsageLimits
    {
        $url = $this->authenticatorUrl.'/api/teams/'.$slug.'/usage-limits';

        try {
            $response = $this->get($url);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw TeamNotFound::createFromSlug($slug);
            }

            throw $e;
        }

        $usageLimits = $this->serializer->deserialize($response->getBody()->getContents(), TeamUsageLimits::class, 'json');

        return $usageLimits;
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountByUuid(UuidInterface $uuid)
    {
        $url = $this->authenticatorUrl.'/api/accounts/'.$uuid->toString();

        try {
            $response = $this->get($url);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new AccountNotFound(sprintf('Account "%s" is not found', $uuid->toString()));
            }

            throw $e;
        }

        $account = $this->serializer->deserialize($response->getBody()->getContents(), Account::class, 'json');

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function findAccountsByUser(string $username)
    {
        $response = $this->get($this->authenticatorUrl.'/api/users/'.$username.'/accounts');

        return $this->serializer->deserialize(
            $response->getBody()->getContents(),
            'array<'.Account::class.'>',
            'json'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findAllTeams()
    {
        $url = $this->authenticatorUrl.'/api/teams';

        try {
            $response = $this->get($url);
        } catch (ClientException $e) {
            return [];
        }

        return $this->serializer->deserialize(
            $response->getBody()->getContents(),
            sprintf('array<%s>', Team::class),
            'json'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey($key)
    {
        try {
            $response = $this->get($this->authenticatorUrl . '/api/api-keys/'.$key.'/user');
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return null;
            }

            throw $e;
        }

        return $this->serializer->deserialize($response->getBody()->getContents(), User::class, 'json');
    }

    /**
     * {@inheritdoc}
     */
    public function createApiKey(User $user, string $description)
    {
        try {
            $response = $this->post($this->authenticatorUrl.'/api/user/'.$user->getUsername().'/api-keys', [
                'description' => $description,
            ]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return null;
            }

            throw $e;
        }

        return $this->serializer->deserialize($response->getBody()->getContents(), UserApiKey::class, 'json');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTeamBySlug(string $slug)
    {
        try {
            $this->delete($this->authenticatorUrl.'/api/teams/'.$slug);
        } catch (ClientException $e) {
            if (Response::HTTP_NOT_FOUND == $e->getResponse()->getStatusCode()) {
                throw TeamNotFound::createFromSlug($slug);
            }

            throw new OperationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function get($url)
    {
        return $this->request('get', $url);
    }

    private function post($url, $body)
    {
        return $this->request('post', $url, [
            'json' => $body,
        ]);
    }

    private function patch($url, $body)
    {
        return $this->request('patch', $url, [
            'json' => $body,
        ]);
    }

    private function delete($url)
    {
        return $this->request('delete', $url);
    }

    /**
     * @param $method
     * @param string $url
     * @param array $options
     *
     * @throws RequestException
     *
     * @return ResponseInterface
     */
    private function request($method, $url, array $options = [])
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $this->httpClient->$method($url, array_merge([
            'headers' => [
                'X-Api-Key' => $this->authenticationToken,
            ],
        ], $options));

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->seek(0);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function addDockerRegistryToBucket(UuidInterface $bucketUuid, DockerRegistry $credentials)
    {
        $url = $this->authenticatorUrl.'/api/bucket/'.$bucketUuid->toString().'/docker-registries';

        try {
            $this->post($url, \GuzzleHttp\json_decode(
                $this->serializer->serialize($credentials, 'json'),
                true
            ));
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new BucketNotFound(sprintf('Bucket "%s" is not found', $bucketUuid));
            }

            throw new OperationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addClusterToBucket(UuidInterface $bucketUuid, Cluster $cluster)
    {
        $url = $this->authenticatorUrl.'/api/bucket/'.$bucketUuid->toString().'/clusters';

        try {
            $this->post($url, \GuzzleHttp\json_decode(
                $this->serializer->serialize($cluster, 'json'),
                true
            ));
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new BucketNotFound(sprintf('Bucket "%s" is not found', $bucketUuid));
            }

            throw new OperationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateRegistryAttributes(UuidInterface $bucketUuid, string $address, array $attributes)
    {
        $url = $this->authenticatorUrl.'/api/bucket/'.$bucketUuid->toString().'/docker-registries/'.urlencode($address);

        try {
            $this->patch($url, \GuzzleHttp\json_encode([
                'attributes' => $attributes,
            ]));
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new BucketNotFound(sprintf('Bucket "%s" is not found', $bucketUuid));
            }

            throw new OperationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
