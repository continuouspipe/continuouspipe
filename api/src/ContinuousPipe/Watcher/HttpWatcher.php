<?php

namespace ContinuousPipe\Watcher;

use ContinuousPipe\Security\Credentials\Cluster;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use LogStream\Tree\TreeLog;

class HttpWatcher implements Watcher
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
     * @param ClientInterface $httpClient
     * @param string          $baseUrl
     */
    public function __construct(ClientInterface $httpClient, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function logs(Cluster\Kubernetes $kubernetes, string $namespace, string $pod)
    {
        try {
            $response = $this->httpClient->post($this->baseUrl.'/v1/watch/logs', [
                'json' => [
                    'cluster' => [
                        'address' => $kubernetes->getAddress(),
                        'version' => 'v1',
                        'username' => $kubernetes->getUsername(),
                        'password' => $kubernetes->getPassword(),
                    ],
                    'namespace' => $namespace,
                    'pod' => $pod,
                ],
            ]);
        } catch (RequestException $e) {
            throw new WatcherException('Unable to connect to the watcher', $e->getCode(), $e);
        }

        try {
            $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (\InvalidArgumentException $e) {
            throw new WatcherException('The response is not a JSON as expected');
        }

        return TreeLog::fromId($json['logId']);
    }
}
