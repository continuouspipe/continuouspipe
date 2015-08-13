<?php

namespace ContinuousPipe\User;

use ContinuousPipe\Authenticator\Security\Authentication\UserDetails;
use GuzzleHttp\Client;

class GitHubUserDetails implements UserDetails
{
    const GITHUB_API = 'https://api.github.com';

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailAddress($token)
    {
        $response = $this->getEmailAddresses($token);

        foreach (json_decode($response->getBody()) as $address) {
            if ($address->primary) {
                return $address->email;
            }
        }

        throw new EmailNotFoundException();
    }

    /**
     * @param string $token
     * @return GuzzleHttp\Message\Response
     */
    private function getEmailAddresses($token)
    {
        return $this->client->get(self::GITHUB_API . '/user/emails', [
            'headers' => ['Authorization' => 'token ' . $token ]
        ]);
    }
}
