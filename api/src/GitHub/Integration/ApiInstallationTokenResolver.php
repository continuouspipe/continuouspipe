<?php

namespace GitHub\Integration;

use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class ApiInstallationTokenResolver implements InstallationTokenResolver
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var int
     */
    private $integrationId;

    /**
     * @param Client              $httpClient
     * @param JWTEncoderInterface $jwtEncoder
     * @param SerializerInterface $serializer
     * @param int                 $integrationId
     */
    public function __construct(Client $httpClient, JWTEncoderInterface $jwtEncoder, SerializerInterface $serializer, $integrationId)
    {
        $this->httpClient = $httpClient;
        $this->jwtEncoder = $jwtEncoder;
        $this->serializer = $serializer;
        $this->integrationId = $integrationId;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Installation $installation)
    {
        $now = time();

        $jwt = $this->jwtEncoder->encode([
            'iat' => $now,
            'exp' => $now + 60,
            'iss' => $this->integrationId,
        ]);

        $response = $this->httpClient->post('https://api.github.com/installations/'.$installation->getId().'/access_tokens', [
            'headers' => [
                'Accept' => 'application/vnd.github.machine-man-preview+json',
                'Authorization' => 'Bearer '.$jwt,
            ],
        ]);

        return $this->serializer->deserialize($response->getBody()->getContents(), InstallationToken::class, 'json');
    }
}
