<?php

namespace GitHub\Integration;

use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class ApiInstallationRepository implements InstallationRepository
{
    /**
     * @var Client
     */
    private $client;

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
     * @param Client $client
     * @param JWTEncoderInterface $jwtEncoder
     * @param SerializerInterface $serializer
     * @param int $integrationId
     */
    public function __construct(Client $client, JWTEncoderInterface $jwtEncoder, SerializerInterface $serializer, $integrationId)
    {
        $this->client = $client;
        $this->jwtEncoder = $jwtEncoder;
        $this->serializer = $serializer;
        $this->integrationId = $integrationId;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $now = time();

        $jwt = $this->jwtEncoder->encode([
            'iat' => $now,
            'exp' => $now + 60,
            'iss' => $this->integrationId,
        ]);

        $response = $this->client->get('https://api.github.com/integration/installations', [
            'headers' => [
                'Accept' => 'application/vnd.github.machine-man-preview',
                'Authorization' => 'Bearer ' . $jwt,
            ],
        ]);

        return $this->serializer->deserialize($response->getBody()->getContents(), 'array<'.Installation::class.'>', 'json');
    }

    /**
     * {@inheritdoc}
     */
    public function findByAccount($account)
    {
        $matchingInstallations = array_filter($this->findAll(), function(Installation $installation) use ($account) {
            return $installation->getAccount()->getLogin() == $account;
        });

        if (count($matchingInstallations) == 0) {
            throw new InstallationNotFound(sprintf('No installation found on account "%s"', $account));
        }

        return current($matchingInstallations);
    }
}
