<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Request\BuildRequest;
use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;

class BuilderClient
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
     * @param Client $httpClient
     * @param SerializerInterface $serializer
     * @param string $baseUrl
     */
    public function __construct(Client $httpClient, SerializerInterface $serializer, $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
    }

    /**
     * Start an image build.
     *
     * @param BuildRequest $buildRequest
     *
     * @return BuilderBuild
     */
    public function build(BuildRequest $buildRequest)
    {
        $response = $this->httpClient->post($this->baseUrl.'/build', [
            'body' => $this->serializer->serialize($buildRequest, 'json'),
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        $build = $this->serializer->deserialize($response->getBody()->getContents(), BuilderBuild::class, 'json');

        return $build;
    }
}
