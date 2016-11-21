<?php

namespace ContinuousPipe\HealthChecker;

use ContinuousPipe\Security\Credentials\Cluster;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;

final class HttpHeathCheckClient implements HealthCheckerClient
{
    /**
     * @var ClientInterface
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
     * @param ClientInterface     $httpClient
     * @param SerializerInterface $serializer
     * @param string              $baseUrl
     */
    public function __construct(ClientInterface $httpClient, SerializerInterface $serializer, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function findProblems(Cluster $cluster)
    {
        if (!$cluster instanceof Cluster\Kubernetes) {
            throw new HealthCheckerException('Only supports Kubernetes clusters');
        }

        try {
            $response = $this->httpClient->post($this->baseUrl.'/diagnose-cluster', [
                'json' => [
                    'address' => $cluster->getAddress(),
                    'username' => $cluster->getUsername(),
                    'password' => $cluster->getPassword(),
                ],
            ]);
        } catch (RequestException $e) {
            throw new HealthCheckerException($e->getMessage(), $e->getCode(), $e);
        }

        $body = $response->getBody()->getContents();

        try {
            $problems = $this->serializer->deserialize($body, 'array<'.Problem::class.'>', 'json');
        } catch (\Exception $e) {
            throw new HealthCheckerException('Unable to deserialize response from health-checker', $e->getCode(), $e);
        }

        return $problems;
    }
}
