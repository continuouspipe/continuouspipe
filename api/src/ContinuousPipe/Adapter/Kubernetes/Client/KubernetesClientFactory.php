<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client;

use ContinuousPipe\Adapter\Kubernetes\KubernetesProvider;
use JMS\Serializer\Serializer;
use Kubernetes\Client\Adapter\Http\AuthenticationMiddleware;
use Kubernetes\Client\Adapter\Http\GuzzleHttpClient;
use Kubernetes\Client\Adapter\Http\HttpConnector;
use Kubernetes\Client\Adapter\Http\HttpAdapter;
use Kubernetes\Client\Client;
use Kubernetes\Client\Serializer\JmsSerializerAdapter;

class KubernetesClientFactory
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * @param Serializer $serializer
     * @param \GuzzleHttp\Client $guzzleClient
     */
    public function __construct(Serializer $serializer, \GuzzleHttp\Client $guzzleClient)
    {
        $this->serializer = $serializer;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param KubernetesProvider $provider
     *
     * @return Client
     */
    public function getByProvider(KubernetesProvider $provider)
    {
        $cluster = $provider->getCluster();
        $httpClient = new GuzzleHttpClient(
            $this->guzzleClient,
            $cluster->getAddress(),
            $cluster->getVersion()
        );

        if (null !== ($user = $provider->getUser())) {
            $httpClient = new AuthenticationMiddleware($httpClient, $user->getUsername(), $user->getPassword());
        }

        return new Client(
            new HttpAdapter(
                new HttpConnector(
                    $httpClient,
                    new JmsSerializerAdapter($this->serializer)
                )
            )
        );
    }
}
