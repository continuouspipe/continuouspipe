<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client;

use ContinuousPipe\Security\Credentials\Cluster;
use JMS\Serializer\Serializer;
use Kubernetes\Client\Adapter\Http\AuthenticationMiddleware;
use Kubernetes\Client\Adapter\Http\GuzzleHttpClient;
use Kubernetes\Client\Adapter\Http\HttpConnector;
use Kubernetes\Client\Adapter\Http\HttpAdapter;
use Kubernetes\Client\Client;
use Kubernetes\Client\Serializer\JmsSerializerAdapter;

class HttpClientFactory implements KubernetesClientFactory
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
     * @param Serializer         $serializer
     * @param \GuzzleHttp\Client $guzzleClient
     */
    public function __construct(Serializer $serializer, \GuzzleHttp\Client $guzzleClient)
    {
        $this->serializer = $serializer;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param Cluster\Kubernetes $cluster
     *
     * @return Client
     */
    public function getByCluster(Cluster\Kubernetes $cluster)
    {
        $httpClient = new GuzzleHttpClient(
            $this->guzzleClient,
            $cluster->getAddress(),
            $this->getClusterVersion($cluster)
        );

        if (null !== $cluster->getUsername()) {
            $httpClient = new AuthenticationMiddleware($httpClient, $cluster->getUsername(), $cluster->getPassword());
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

    /**
     * @param Cluster $cluster
     * 
     * @return string
     */
    private function getClusterVersion(Cluster $cluster)
    {
        return explode('.', $cluster)[0];
    }
}
