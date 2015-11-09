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
use GuzzleHttp\Message\ResponseInterface;
use JMS\Serializer\Serializer;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;

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
        $response = $this->client->post($this->baseUrl.'/deployments', [
            'body' => $this->serializer->serialize($deploymentRequest, 'json'),
            'headers' => $this->getRequestHeaders($user),
        ]);

        $contents = $this->getResponseContents($response);
        $deployment = $this->serializer->deserialize($contents, Deployment::class, 'json');

        return $deployment;
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

        $this->client->delete($url, [
            'headers' => $this->getRequestHeaders($authenticatedUser),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($clusterIdentifier, Team $team, User $authenticatedUser)
    {
        $url = sprintf(
            $this->baseUrl.'/teams/%s/clusters/%s/environments',
            $team->getSlug(),
            $clusterIdentifier
        );

        try {
            $response = $this->client->get($url, [
                'headers' => $this->getRequestHeaders($authenticatedUser),
            ]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new ClusterNotFound(sprintf(
                    'Cluster named "%s" is not found',
                    $clusterIdentifier
                ));
            }

            throw $e;
        }

        $contents = $this->getResponseContents($response);
        $environments = $this->serializer->deserialize($contents, 'array<'.Environment::class.'>', 'json');

        return $environments;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function getRequestHeaders(User $user)
    {
        $token = $this->jwtManager->create(new SecurityUser($user));

        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
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
}
