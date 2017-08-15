<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client;

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

    public function __construct(
        SerializerInterface        $serializer,
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

        if (null !== $cluster->getGoogleCloudServiceAccount()) {
            $httpClient = new AuthenticationMiddleware($httpClient, AuthenticationMiddleware::TOKEN, $this->getTokenFromGoogleCloudServiceAccount($cluster->getGoogleCloudServiceAccount()));
        } elseif (null !== $cluster->getClientCertificate()) {
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

    private function getClusterVersion(Cluster\Kubernetes $cluster) : string
    {
        return explode('.', $cluster->getVersion())[0];
    }

    private function getTokenFromGoogleCloudServiceAccount(string $serviceAccountAsString) : string
    {
        try {
            $serviceAccount = \GuzzleHttp\json_decode(base64_decode($serviceAccountAsString), true);
        } catch (\InvalidArgumentException $e) {
            throw new ClientException('Service account is not a valid JSON: '.$e->getMessage(), $e->getCode(), $e);
        }

        $credentials = new ServiceAccountCredentials('https://www.googleapis.com/auth/cloud-platform', $serviceAccount);
        try {
            $token = $credentials->fetchAuthToken();
        } catch (\RuntimeException $e) {
            throw new ClientException('Can\'t get token from Google Cloud: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (!isset($token['access_token'])) {
            throw new ClientException('Access token could not be found in Google Auth response');
        }

        return $token['access_token'];
    }
}
