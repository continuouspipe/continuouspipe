<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Billing\BillingException;
use ContinuousPipe\Security\Team\Team;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;

class AuthenticatorHttpBillingProfileRepository implements BillingProfileRepository
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
    private $authenticatorUrl;

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(ClientInterface $httpClient, SerializerInterface $serializer, string $authenticatorUrl, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->apiKey = $apiKey;
        $this->authenticatorUrl = $authenticatorUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team): BillingProfile
    {
        try {
            $response = $this->httpClient->request('get', $this->authenticatorUrl . '/api/teams/' . $team->getSlug() . '/billing-profile', [
                'headers' => [
                    'X-Api-Key' => $this->apiKey,
                ]
            ]);

            return $this->serializer->deserialize($response->getBody()->getContents(), BillingProfile::class, 'json');
        } catch (RequestException $e) {
            if ($e->getCode() == 404) {
                throw new BillingProfileNotFound(sprintf('Billing profile for team "%s" was not found', $team->getSlug()));
            }

            throw new BillingException('Could not get team\'s billing profile', $e->getCode(), $e);
        }
    }
}
