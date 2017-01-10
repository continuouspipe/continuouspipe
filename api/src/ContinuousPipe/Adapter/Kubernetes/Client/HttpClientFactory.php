<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client;

use ContinuousPipe\Security\Credentials\Cluster;
use GuzzleHttp\Client as GuzzleClient;
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
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * @var FaultToleranceConfigurator
     */
    private $faultToleranceConfigurator;

    public function __construct(
        Serializer                 $serializer,
        GuzzleClient               $guzzleClient,
        FaultToleranceConfigurator $faultToleranceConfigurator)
    {
        $this->serializer = $serializer;
        $this->guzzleClient = $guzzleClient;
        $this->faultToleranceConfigurator = $faultToleranceConfigurator;
    }

    /**
     * @param Cluster\Kubernetes $cluster
     *
     * @return Client
     */
    public function getByCluster(Cluster\Kubernetes $cluster)
    {
        $this->faultToleranceConfigurator->configureToBeFaultTolerant($this->guzzleClient);

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
     * @param Cluster\Kubernetes $cluster
     *
     * @return string
     */
    private function getClusterVersion(Cluster\Kubernetes $cluster)
    {
        return explode('.', $cluster->getVersion())[0];
    }
}
