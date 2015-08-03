<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\User\SecurityUser;
use ContinuousPipe\User\User;
use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;

class HttpBuilderClient implements BuilderClient
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $baseUrl;
    /**
     * @var JWTManagerInterface
     */
    private $jwtManager;

    /**
     * @param Client              $httpClient
     * @param SerializerInterface $serializer
     * @param JWTManagerInterface $jwtManager
     * @param string              $baseUrl
     */
    public function __construct(Client $httpClient, SerializerInterface $serializer, JWTManagerInterface $jwtManager, $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
        $this->jwtManager = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildRequest $buildRequest, User $user)
    {
        $token = $this->jwtManager->create(new SecurityUser($user));
        $response = $this->httpClient->post($this->baseUrl.'/build', [
            'body' => $this->serializer->serialize($buildRequest, 'json'),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        $build = $this->serializer->deserialize($response->getBody()->getContents(), BuilderBuild::class, 'json');

        return $build;
    }
}
