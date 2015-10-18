<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\User\SecurityUser;
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
    public function deleteEnvironment(DeploymentRequest\Target $target, User $user)
    {
        $url = sprintf(
            $this->baseUrl.'/providers/%s/environments/%s',
            $target->getProviderName(),
            $target->getEnvironmentName()
        );

        $this->client->delete($url, [
            'headers' => $this->getRequestHeaders($user),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironments($providerName, User $user)
    {
        $url = sprintf(
            $this->baseUrl.'/providers/%s/environments',
            $providerName
        );

        try {
            $response = $this->client->get($url, [
                'headers' => $this->getRequestHeaders($user),
            ]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new ProviderNotFound(sprintf(
                    'Provider named "%s" is not found',
                    $providerName
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
