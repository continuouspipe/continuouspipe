<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;
use GuzzleHttp\Client as GuzzleClient;
use JMS\Serializer\Serializer;

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
     * @var string
     */
    private $baseUrl;

    /**
     * @param GuzzleClient $client
     * @param Serializer   $serializer
     * @param string       $baseUrl
     */
    public function __construct(GuzzleClient $client, Serializer $serializer, $baseUrl)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function start(EnvironmentDeploymentRequest $deploymentRequest)
    {
        $this->client->put($this->baseUrl.'/environments', [
            'body' => $this->serializer->serialize($deploymentRequest, 'json'),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
