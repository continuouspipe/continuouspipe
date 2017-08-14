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
use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Serializer                 $serializer,
        GuzzleClient               $guzzleClient,
        FaultToleranceConfigurator $faultToleranceConfigurator,
        LoggerInterface            $logger
    ) {
        $this->serializer = $serializer;
        $this->guzzleClient = $guzzleClient;
        $this->faultToleranceConfigurator = $faultToleranceConfigurator;
        $this->logger = $logger;
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

        if (null !== $cluster->getClientCertificate()) {
            $httpClient = new AuthenticationMiddleware($httpClient, AuthenticationMiddleware::CERTIFICATE, $cluster->getClientCertificate());
        } else if (null !== $cluster->getUsername()) {
            $httpClient = new AuthenticationMiddleware($httpClient, AuthenticationMiddleware::USERNAME_PASSWORD, sprintf('%s:%s', $cluster->getUsername(), $cluster->getPassword()));
        }

        return new Client(
            new HttpAdapter(
                new HttpConnector(
                    $httpClient,
                    new JmsSerializerAdapter($this->serializer),
                    $this->logger
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
