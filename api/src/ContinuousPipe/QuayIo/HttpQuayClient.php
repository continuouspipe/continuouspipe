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

    /**
     * @var string
     */
    private $organisation;

    /**
     * @var string
     */
    private $accessToken;

    public function __construct(ClientInterface $httpClient, string $organisation, string $accessToken)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = 'https://quay.io/api/v1';
        $this->organisation = $organisation;
        $this->accessToken = $accessToken;
    }

    public function createRobotAccount(string $name): RobotAccount
    {
        $robot = $this->json(
            $this->request(
                'put',
                sprintf($this->baseUrl.'/organization/%s/robots/%s', $this->organisation, $name)
            )
        );

        return new RobotAccount(
            $robot['name'],
            $robot['token'],
            'robot+'.$name.'@continuouspipe.net'
        );
    }

    public function createRepository(string $name): Repository
    {
        try {
            $repository = $this->json(
                $this->request('post', $this->baseUrl . '/repository', [
                    'json' => [
                        'namespace' => $this->organisation,
                        'repository' => $name,
                        'visibility' => 'public',
                        'description' => $name,
                    ]
                ])
            );
        } catch (RepositoryAlreadyExists $e) {
            throw $e->withRepository(new Repository($this->organisation.'/'.$name));
        }

        return new Repository(
            $repository['namespace'].'/'.$repository['name']
        );
    }

    public function allowUserToAccessRepository(string $username, string $repositoryName)
    {
        $this->request(
            'put',
            $this->baseUrl . '/repository/'.$repositoryName.'/permissions/user/'.$username,
            [
                'json' => [
                    'role' => 'write',
                ]
            ]
        );
    }

    private function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try {
            return $this->httpClient->request($method, $url, array_merge([
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
            ], $options));
        } catch (RequestException $e) {
            if (null !== ($response = $e->getResponse())) {
                try {
                    $json = $this->json($response);

                    if (isset($json['error_message']) && $json['error_message'] == 'Repository already exists') {
                        throw new RepositoryAlreadyExists($e);
                    }
                } catch (\Throwable $sub) {
                    // We can't get anything from the response, fallback on the default
                    // catching behaviour
                }
            }

            throw new QuayException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function json(ResponseInterface $response) : array
    {
        try {
            return \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (\InvalidArgumentException $e) {
            throw new QuayException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
