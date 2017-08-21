<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client;

use ContinuousPipe\Adapter\Kubernetes\Client\Authentication\AuthenticatedHttpClientFactory;
use ContinuousPipe\Security\Credentials\Cluster;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\ClientInterface as GuzzleClient;
use JMS\Serializer\SerializerInterface;
use Kubernetes\Client\Adapter\Http\AuthenticationMiddleware;
use Kubernetes\Client\Adapter\Http\GuzzleHttpClient;
use Kubernetes\Client\Adapter\Http\HttpConnector;
use Kubernetes\Client\Adapter\Http\HttpAdapter;
use Kubernetes\Client\Client;
use Kubernetes\Client\Serializer\JmsSerializerAdapter;
use Psr\Log\LoggerInterface;

class HttpClientFactory implements KubernetesClientFactory
{
    /**
     * @var SerializerInterface
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

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var AuthenticatedHttpClientFactory
     */
    private $authenticatedHttpClientFactory;

    public function __construct(
        SerializerInterface        $serializer,
        GuzzleClient               $guzzleClient,
        FaultToleranceConfigurator $faultToleranceConfigurator,
        AuthenticatedHttpClientFactory $authenticatedHttpClientFactory,
        LoggerInterface            $logger
    ) {
        $this->serializer = $serializer;
        $this->guzzleClient = $guzzleClient;
        $this->faultToleranceConfigurator = $faultToleranceConfigurator;
        $this->logger = $logger;
        $this->authenticatedHttpClientFactory = $authenticatedHttpClientFactory;
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
            $this->getClusterVersion($cluster),
            $cluster->getCaCertificate()
        );

        return new Client(
            new HttpAdapter(
                new HttpConnector(
                    $this->authenticatedHttpClientFactory->authenticatedClient($httpClient, $cluster),
                    new JmsSerializerAdapter($this->serializer),
                    $this->logger
                )
            )
        );
    }

    private function getClusterVersion(Cluster\Kubernetes $cluster) : string
    {
        return explode('.', $cluster->getVersion())[0];
    }
}
