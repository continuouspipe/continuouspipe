<?php

namespace ContinuousPipe\UserDetails;

use GuzzleHttp\Client;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

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
            if ($address->primary === true) {
                return $address->email;
            }
        }

        throw new UnsupportedUserException('User must have an email');
    }

    /**
     * @param string $token
     */
    private function getEmailAddresses($token)
    {
        return $this->client->get(self::GITHUB_API . '/user/emails', [
            'headers' => ['Authorization' => 'token ' . $token ]
        ]);
    }
}
