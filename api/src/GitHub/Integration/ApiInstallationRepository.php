<?php

namespace GitHub\Integration;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\InstallationClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
     * @var InstallationClientFactory
     */
    private $clientFactory;

    /**
     * @var int
     */
    private $integrationId;

    /**
     * @param Client                    $client
     * @param JWTEncoderInterface       $jwtEncoder
     * @param SerializerInterface       $serializer
     * @param InstallationClientFactory $clientFactory
     * @param int                       $integrationId
     */
    public function __construct(Client $client, JWTEncoderInterface $jwtEncoder, SerializerInterface $serializer, InstallationClientFactory $clientFactory, $integrationId)
    {
        $this->client = $client;
        $this->jwtEncoder = $jwtEncoder;
        $this->serializer = $serializer;
        $this->integrationId = $integrationId;
        $this->clientFactory = $clientFactory;
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
                'Authorization' => 'Bearer '.$jwt,
            ],
        ]);

        return $this->serializer->deserialize($response->getBody()->getContents(), 'array<'.Installation::class.'>', 'json');
    }

    /**
     * {@inheritdoc}
     */
    public function findByRepository(GitHubCodeRepository $codeRepository)
    {
        $matchingInstallations = array_filter($this->findAll(), function (Installation $installation) use ($codeRepository) {
            return $installation->getAccount()->getLogin() == $codeRepository->getOrganisation();
        });

        if (count($matchingInstallations) == 0) {
            throw new InstallationNotFound('The GitHub integration is not installed');
        }

        $failedInstallations = [];
        foreach ($matchingInstallations as $installation) {
            $client = $this->clientFactory->createClientFromInstallation($installation);

            try {
                $client->repo()->show(
                    $codeRepository->getOrganisation(),
                    $codeRepository->getName()
                );

                return $installation;
            } catch (RequestException $e) {
                $failedInstallations[] = $installation;
            }
        }

        throw new InstallationNotFound('The GitHub integration does not have access to this repository');
    }
}
