<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;
use ContinuousPipe\User\SecurityUser;
use ContinuousPipe\User\User;
use GuzzleHttp\Client as GuzzleClient;
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
     * @param GuzzleClient $client
     * @param Serializer $serializer
     * @param JWTManagerInterface $jwtManager
     * @param string $baseUrl
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
    public function start(EnvironmentDeploymentRequest $deploymentRequest, User $user)
    {
        $token = $this->jwtManager->create(new SecurityUser($user));
        $this->client->put($this->baseUrl.'/environments', [
            'body' => $this->serializer->serialize($deploymentRequest, 'json'),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);
    }
}
