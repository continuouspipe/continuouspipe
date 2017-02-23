<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use Github\HttpClient\HttpClientInterface;

class TestHttpClient implements HttpClientInterface
{
    /**
     * @var array
     */
    private $requests = [];

    /**
     * @var callable[]
     */
    private $hooks = [];

    /**
     * {@inheritdoc}
     */
    public function get($path, array $parameters = array(), array $headers = array())
    {
        return $this->request($path, null, 'GET', $headers, array('query' => $parameters));
    }

    /**
     * {@inheritdoc}
     */
    public function post($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'POST', $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function patch($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'PATCH', $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'DELETE', $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $body, array $headers = array())
    {
        return $this->request($path, $body, 'PUT', $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function request($path, $body, $httpMethod = 'GET', array $headers = array())
    {
        $this->requests[] = [
            'path' => $path,
            'method' => $httpMethod,
            'body' => $body,
        ];

        foreach ($this->hooks as $hook) {
            if ($response = $hook($path, $body, $httpMethod, $headers)) {
                return $response;
            }
        }

        return new \Guzzle\Http\Message\Response(
            $httpMethod == 'HEAD' ? 404 : 200,
            [],
            ''
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($tokenOrLogin, $password, $authMethod)
    {
    }

    /**
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @param callable $hook
     */
    public function addHook(callable $hook)
    {
        $this->hooks[] = $hook;
    }
}
