<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Google\Token\Token;
use ContinuousPipe\Google\Token\TokenResolver;
use ContinuousPipe\Security\Account\GoogleAccount;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;

class HttpTokenResolver implements TokenResolver
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ClientInterface     $client
     * @param SerializerInterface $serializer
     * @param string              $clientId
     * @param string              $clientSecret
     */
    public function __construct(ClientInterface $client, SerializerInterface $serializer, string $clientId, string $clientSecret)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function forAccount(GoogleAccount $account)
    {
        try {
            if (null !== ($serviceAccount = $account->getServiceAccount())) {
                $fetcher = new ServiceAccountCredentials('https://www.googleapis.com/auth/cloud-platform', \GuzzleHttp\json_decode(base64_decode($serviceAccount), true));
                $tokenAsString = \GuzzleHttp\json_encode($fetcher->fetchAuthToken());
            } else {
                $tokenAsString = $this->tokenFromRefreshToken($account->getRefreshToken());
            }
        } catch (RequestException $e) {
            throw GoogleHttpUtils::createGoogleExceptionFromRequestException($e);
        }

        try {
            return $this->serializer->deserialize($tokenAsString, Token::class, 'json');
        } catch (\InvalidArgumentException $e) {
            throw new GoogleException('Token from Google\'s API was malformed', 500, $e);
        }
    }

    /**
     * @param string $refreshToken
     *
     * @return string
     */
    private function tokenFromRefreshToken(string $refreshToken): string
    {
        $response = $this->client->request('POST', 'https://www.googleapis.com/oauth2/v4/token', [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
        ]);

        return $response->getBody()->getContents();
    }
}
