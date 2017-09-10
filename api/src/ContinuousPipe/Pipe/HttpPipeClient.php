<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use JMS\Serializer\Serializer;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class HttpPipeClient implements Client
{
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var JWTManagerInterface
     */
    private $jwtManager;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param GuzzleClient        $client
     * @param Serializer          $serializer
     * @param JWTManagerInterface $jwtManager
     * @param string              $baseUrl
     */
    public function __construct(GuzzleClient $client, Serializer $serializer, JWTManagerInterface $jwtManager, $baseUrl)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->baseUrl = $baseUrl;
        $this->jwtManager = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function start(DeploymentRequest $deploymentRequest, User $user)
    {
        try {
            $response = $this->client->request('post', $this->baseUrl . '/deployments', [
                'body' => $this->serializer->serialize($deploymentRequest, 'json'),
                'headers' => $this->getRequestHeadersForUser($user),
            ]);
        } catch (RequestException $e) {
            throw new PipeClientException('Something went wrong while starting the deployment: '.$e->getMessage(), $e->getCode(), $e);
        }

        try {
            return $this->serializer->deserialize($this->getResponseContents($response), Deployment::class, 'json');
        } catch (\InvalidArgumentException $e) {
            throw new PipeClientException('Response from pipe is not understandable: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEnvironment(DeploymentRequest\Target $target, Team $team, User $authenticatedUser)
    {
        $url = sprintf(
            $this->baseUrl.'/teams/%s/clusters/%s/environments/%s',
            $team->getSlug(),
            $target->getClusterIdentifier(),
            $target->getEnvironmentName()
        );

        try {
            $this->client->request('delete', $url, [
                'headers' => $this->getRequestHeadersForUser($authenticatedUser),
            ]);
        } catch (RequestException $e) {
            throw new PipeClientException('Something went wrong while deleting environment: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletePod(Team $team, User $authenticatedUser, string $clusterIdentifier, string $namespace, string $podName)
    {
        $url = sprintf(
            $this->baseUrl.'/teams/%s/clusters/%s/namespaces/%s/pods/%s',
            $team->getSlug(),
            $clusterIdentifier,
            $namespace,
            $podName
        );

        try {
            $this->client->request('delete', $url, [
                'headers' => $this->getRequestHeadersForUser($authenticatedUser),
            ]);
        } catch (RequestException $e) {
            if (null !== ($response = $e->getResponse())) {
                if ($response->getStatusCode() == 404) {
                    throw new PodNotFound(sprintf('Pod %s not found', $podName));
                }
            }

            throw new PipeClientException('Something went wrong while deleting the pod: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($clusterIdentifier, Team $team)
    {
        $url = sprintf(
            $this->baseUrl.'/teams/%s/clusters/%s/environments',
            $team->getSlug(),
            $clusterIdentifier
        );

        return $this->requestEnvironmentList($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentsLabelled($clusterIdentifier, Team $team, array $labels)
    {
        $queryFilters = [
            'labels' => $labels,
        ];

        $url = sprintf($this->baseUrl.'/teams/%s/clusters/%s/environments', $team->getSlug(), $clusterIdentifier);
        $url .= '?'.http_build_query($queryFilters);

        return $this->requestEnvironmentList($url);
    }

    /**
     * @param User|UserInterface $user
     *
     * @return array
     */
    private function getRequestHeadersForUser($user)
    {
        if ($user instanceof User) {
            $user = new SecurityUser($user);
        }

        if (!$user instanceof UserInterface) {
            throw new \InvalidArgumentException('Expected to have a user object');
        }

        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->jwtManager->create($user),
        ];
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string
     */
    private function getResponseContents(ResponseInterface $response)
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->seek(0);
        }

        return $body->getContents();
    }

    /**
     * @param User   $user
     * @param string $url
     *
     * @throws ClusterNotFound
     * @throws PipeClientException
     *
     * @return PromiseInterface Array of Environment objects.
     */
    private function requestEnvironmentList($url)
    {
        $httpPromise = $this->client->requestAsync('get', $url, [
            'headers' => $this->getRequestHeadersForUser(new \Symfony\Component\Security\Core\User\User('system:river', null)),
        ]);

        $environmentPromise = $httpPromise->then(
            function (ResponseInterface $response) {
                $contents = $this->getResponseContents($response);
                $environments = $this->serializer->deserialize($contents, 'array<'.Environment::class.'>', 'json');

                return $environments;
            },
            function (RequestException $e) {
                if (null !== ($response = $e->getResponse())) {
                    if ($response->getStatusCode() == 404) {
                        throw new ClusterNotFound('Unable to get the environment list');
                    }
                }

                throw new PipeClientException($e->getMessage(), $e->getCode(), $e);
            }
        );

        return $environmentPromise;
    }
}
