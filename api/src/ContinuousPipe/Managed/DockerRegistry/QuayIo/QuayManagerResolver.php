<?php

namespace ContinuousPipe\Managed\DockerRegistry\QuayIo;

use ContinuousPipe\Managed\DockerRegistry\DockerRegistryManager;
use ContinuousPipe\Managed\DockerRegistry\DockerRegistryManagerResolver;
use ContinuousPipe\QuayIo\HttpQuayClient;
use ContinuousPipe\Security\Credentials\BucketRepository;
use GuzzleHttp\ClientInterface;

class QuayManagerResolver implements DockerRegistryManagerResolver
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct(BucketRepository $bucketRepository, ClientInterface $httpClient)
    {
        $this->bucketRepository = $bucketRepository;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $dsn): DockerRegistryManager
    {
        $parsedUrl = parse_url($dsn);

        return new QuayManager(
            new HttpQuayClient(
                $this->httpClient,
                $parsedUrl['host'],
                $parsedUrl['pass']
            ),
            $this->bucketRepository
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $dsn): bool
    {
        return ($parsedUrl = parse_url($dsn)) && $parsedUrl['scheme'] == 'quay';
    }
}
