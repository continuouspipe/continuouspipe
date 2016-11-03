<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\GoogleException;
use ContinuousPipe\Google\Token\Token;
use ContinuousPipe\Google\Token\TokenResolver;
use ContinuousPipe\Security\Account\GoogleAccount;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;

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
            $response = $this->client->request('POST', 'https://www.googleapis.com/oauth2/v4/token', [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $account->getRefreshToken(),
                ],
            ]);
        } catch (RequestException $e) {
            throw GoogleHttpUtils::createGoogleExceptionFromRequestException($e);
        }

        try {
            return $this->serializer->deserialize($response->getBody()->getContents(), Token::class, 'json');
        } catch (\Exception $e) {
            throw new GoogleException('Unexpected response ('.$response->getStatusCode().')', 500, $e);
        }
    }
}
