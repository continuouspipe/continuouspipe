<?php

namespace ContinuousPipe\Builder\Image;

use ContinuousPipe\Builder\Docker\CredentialsRepository;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Security\Authenticator\CredentialsNotFound;
use ContinuousPipe\Security\Credentials\DockerRegistry as DockerRegistryCredentials;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DockerRegistry implements Registry
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CredentialsRepository
     */
    private $credentialsRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ClientInterface $client,
        CredentialsRepository $credentialsRepository,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->credentialsRepository = $credentialsRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function containsImage(UuidInterface $credentialsBucket, Image $image): bool
    {
        try {
            $registry = $this->credentialsRepository->findRegistryByImage(
                $image,
                $credentialsBucket
            );
        } catch (CredentialsNotFound $e) {
            throw new SearchingForExistingImageException('Cannot check image presence without credentials', $e->getCode(), $e);
        }

        $manifestResponse = $this->requestManifest($image, $registry);

        if ($manifestResponse->getStatusCode() == 200) {
            return true;
        } elseif ($manifestResponse->getStatusCode() == 404) {
            return false;
        }

        throw new SearchingForExistingImageException('Unable to get from registry if the image already exists. Received status: '.$manifestResponse->getStatusCode());
    }

    private function requestManifest(Image $image, DockerRegistryCredentials $credentials, string $token = null): ResponseInterface
    {
        $url = sprintf(
            'https://%s/v2/%s/manifests/%s',
            $this->serverAddress($credentials->getServerAddress()),
            $image->getTwoPartName(),
            $image->getTag()
        );

        try {
            return $this->client->request(
                'head',
                $url,
                isset($token) ? ['headers' => ['Authorization' => 'Bearer ' . $token]] : []
            );
        } catch (RequestException $e) {
            if (null === ($response = $e->getResponse())) {
                throw new SearchingForExistingImageException('No response from Registry manifest request', $e->getCode(), $e);
            }

            if ($response->getStatusCode() == 401 && null === $token) {
                // Manifest requests requires authentication.
                $token = $this->fetchToken($this->fetchAuthDetails($response), $credentials, $image);

                return $this->requestManifest($image, $credentials, $token);
            }

            return $response;
        }
    }

    private function fetchAuthDetails(ResponseInterface $response) : array
    {
        if (null === $authHeader = $response->getHeaderLine('WWW-Authenticate')) {
            throw new SearchingForExistingImageException('Error retrieving auth details from response header');
        }

        if (false === preg_match_all('/(\w+)="([^"]+)"/', $authHeader, $matches, PREG_SET_ORDER)) {
            throw new SearchingForExistingImageException('Cannot get authentication details from auth header: '.$authHeader);
        }

        //should check all present before returning
        return array_column($matches, 2, 1);
    }

    private function fetchToken(array $authDetails, DockerRegistryCredentials $credentials, Image $image): string
    {
        try {
            $tokenResponse = $this->client->request(
                'get',
                sprintf('%s?service=%s&scope=%s', $authDetails['realm'], $authDetails['service'],
                    $this->scopeNeeded($authDetails, $image)
                ),
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode(
                                $credentials->getUsername() . ':' . $credentials->getPassword()
                            )
                    ],
                ]
            );
        } catch (RequestException $e) {
            throw new SearchingForExistingImageException('Cannot get authentication token from registry', $e->getCode(), $e);
        }

        if ($tokenResponse->getStatusCode() != 200) {
            throw new SearchingForExistingImageException('Expected response 200 from registry, got '.$tokenResponse->getStatusCode());
        }

        try {
            $json = \GuzzleHttp\json_decode($tokenResponse->getBody(), true);
        } catch (\InvalidArgumentException $e) {
            throw new SearchingForExistingImageException('JSON from Docker registry invalid', $e->getCode(), $e);
        }

        if (!isset($json['token'])) {
            throw new SearchingForExistingImageException('Cannot get token from registry\'s response');
        }

        return $json['token'];
    }

    private function serverAddress(string $serverAddress) : string
    {
        if ('docker.io' == $serverAddress) {
            return 'index.docker.io';
        }

        return $serverAddress;
    }

    private function scopeNeeded(array $authDetails, Image $image)
    {
        return isset($authDetails['scope'])
            ? $authDetails['scope']
            : sprintf('repository:%s:pull', $image->getTwoPartName());
    }
}
