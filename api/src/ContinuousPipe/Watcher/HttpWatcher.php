<?php

namespace ContinuousPipe\Watcher;

use ContinuousPipe\Security\Credentials\Cluster;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use LogStream\Tree\TreeLog;
use Psr\Http\Message\ResponseInterface;

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
            $response = $this->httpClient->request('post', $this->baseUrl.'/v1/watch/logs', [
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
            if ($response = $e->getResponse()) {
                $json = $this->getJson($response);

                if (array_key_exists('message', $json)) {
                    $message = $json['message'];
                }
                if (array_key_exists('code', $json)) {
                    $code = (int) $json['code'];

                    if ($code == 404) {
                        $message = sprintf('The pod "%s" is not found. It might have been already replaced by another one or has been deleted.', $pod);
                    }
                }
            }

            throw new WatcherException(
                isset($message) ? $message : 'Unable to connect to the watcher',
                isset($code) ? $code : $e->getCode(),
                $e
            );
        }

        $json = $this->getJson($response);

        return TreeLog::fromId($json['logId']);
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws WatcherException
     *
     * @return ResponseInterface $response
     */
    private function getJson(ResponseInterface $response)
    {
        try {
            return \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (\InvalidArgumentException $e) {
            throw new WatcherException('The response is not a JSON as expected', 500);
        }
    }
}
