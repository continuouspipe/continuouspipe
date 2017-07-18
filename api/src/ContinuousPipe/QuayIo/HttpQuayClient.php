<?php

namespace ContinuousPipe\QuayIo;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class HttpQuayClient implements QuayClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = 'https://quay.io/api/v1';
    }

    public function createRobotAccount(string $name): RobotAccount
    {
        $this->post($this->baseUrl . '/robots', [
            'json' => [
                'name' => $name,
            ]
        ]);

        return new RobotAccount(
            'robot+'.$name,
            'password',
            $name.'@not-required-email.com'
        );
    }

    public function createRepository(string $name): Repository
    {
        $this->post($this->baseUrl . '/repositories', [
            'json' => [
                'name' => $name,
            ]
        ]);

        return new Repository();
    }

    public function allowRobotToAccessRepository(string $robotName, string $repositoryName): void
    {
        $this->post($this->baseUrl . '/repositories/' . $repositoryName . '/permissions', [
            'json' => [
                'robotName' => $robotName,
            ]
        ]);
    }

    private function post(string $url, array $options): ResponseInterface
    {
        try {
            return $this->httpClient->request('post', $url, $options);
        } catch (RequestException $e) {
            throw new QuayException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
