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
        $url = sprintf($this->baseUrl . '/organization/%s/robots/%s', $this->organisation, $name);

        try {
            $robot = $this->json(
                $this->request('put', $url)
            );
        } catch (RobotAlreadyExists $e) {
            $robot = $this->json(
                $this->request('get', $url)
            );
        }

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
            $repository['namespace'].'/'.$repository['name'],
            $repository['visibility'] ?? null
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

    public function changeVisibility(string $repositoryName, string $visibility)
    {
        $this->request(
            'post',
            $this->baseUrl . '/repository/'.$repositoryName.'/change-visibility',
            [
                'json' => [
                    'visibility' => $visibility,
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

                    if (isset($json['message']) && strpos($json['message'], 'Existing robot with name:') === 0) {
                        throw new RobotAlreadyExists($json['message']);
                    }
                } catch (\InvalidArgumentException $sub) {
                    $e = new QuayException('Cannot read from the response: '.$sub->getMessage(), $e->getCode(), $e);
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
