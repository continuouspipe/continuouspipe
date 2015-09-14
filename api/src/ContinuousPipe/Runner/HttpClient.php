<?php

namespace ContinuousPipe\Runner;

use ContinuousPipe\User\SecurityUser;
use ContinuousPipe\User\User;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;
use JMS\Serializer\Serializer;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Rhumsaa\Uuid\Uuid;

class HttpClient implements Client
{
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var JWTManagerInterface
     */
    private $jwtManager;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param GuzzleClient        $client
     * @param Serializer          $serializer
     * @param JWTManagerInterface $jwtManager
     * @param string              $baseUrl
     */
    public function __construct(GuzzleClient $client, Serializer $serializer, JWTManagerInterface $jwtManager, $baseUrl)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->baseUrl = $baseUrl;
        $this->jwtManager = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Client\RunRequest $request, User $user)
    {
        try {
            $response = $this->client->post($this->baseUrl.'/run', [
                'body' => $this->serializer->serialize($request, 'json'),
                'headers' => $this->getRequestHeaders($user),
            ]);
        } catch (RequestException $e) {
            throw new RunnerException(sprintf(
                'Expected status code to be 200 but got %d',
                $e->getResponse() ? $e->getResponse()->getStatusCode() : '0'
            ));
        }

        $contents = json_decode($this->getResponseContents($response));

        return Uuid::fromString($contents['uuid']);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function getRequestHeaders(User $user)
    {
        $token = $this->jwtManager->create(new SecurityUser($user));

        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ];
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string
     */
    private function getResponseContents(ResponseInterface $response)
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->seek(0);
        }

        return $body->getContents();
    }
}
