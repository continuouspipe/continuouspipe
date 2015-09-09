<?php

namespace ContinuousPipe\Adapter\HttpLabs\Endpoint;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Pipe\Environment\PublicEndpoint;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Nocarrier\Hal;
use RMiller\HalGuzzleResponse\GuzzleSubscriber;

class GuzzleEndpointProxier implements EndpointProxier
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->client->getEmitter()->attach(new GuzzleSubscriber());
    }

    public function createProxy(PublicEndpoint $endpoint, $name, Component $component)
    {
        try {
            $stack = $this->createStackForEndpoint($name, $endpoint);
            $this->addMiddlewares($component, $stack);
            $this->deployStack($stack);
        } catch (TransferException $exception) {
            throw new EndpointCouldNotBeProxied("Endpoint proxy $name could not be created", null, $exception);
        }

        return $stack->getData()['url'];
    }

    /**
     * @return array
     */
    private function fetchProjects()
    {
        return $this->client->get($this->projectsLink($this->client))->hal(1)->getResources()['sp:projects'];
    }

    /**
     * @return string
     */
    private function projectsLink()
    {
        return (string) $this->fetchRoot()->getFirstLink('sp:projects');
    }

    /**
     * @return array
     */
    private function getProject()
    {
        return array_reduce(
            $this->getProjects(),
            function ($cpProject, $project) {
                return $project->getData()['name'] == 'continuous pipe' ? $project : $cpProject;
            }
        );
    }

    /**
     * @param string $name
     * @param PublicEndpoint $endpoint
     * @return Hal
     */
    private function createStackForEndpoint($name, PublicEndpoint $endpoint)
    {
        $stack = $this->createStack($name);
        $this->addBackend($endpoint, $stack);

        return $stack;
    }

    /**
     * @param PublicEndpoint $endpoint
     * @param Hal $stack
     */
    private function addBackend(PublicEndpoint $endpoint, Hal $stack)
    {
        $this->client->put($stack->getUri(), ['json' => ['backend' => 'http://' . $endpoint->getAddress()]]);
    }

    /**
     * @return string
     */
    private function templatesLink()
    {
        return (string) $this->fetchRoot()->getFirstLink('sp:templates');
    }

    /**
     * @param array $config
     * @param Hal $stack
     */
    private function addBasicAuth(array $config, Hal $stack)
    {
        if (!isset($config['username']) || !isset($config['password'])) {
            return;
        }

        $this->addMiddleware(
            $stack,
            "basic_authentication",
            [
                'realm' => 'auth needed',
                'username' => $config['username'],
                'password' => $config['password']
            ]
        );
    }

    /**
     * @param array $config
     * @param Hal $stack
     */
    private function addIPWhitelisting(array $config, Hal $stack)
    {
        if (!isset($config['whitelisted-ips'])) {
            return;
        }

        $this->addMiddleware(
            $stack,
            "ip_restrict",
            [
                'ips' => $config['whitelisted-ips'],
            ]
        );
    }

    /**
     * @param Hal $stack
     */
    private function deployStack(Hal $stack)
    {
        $this->client->post((string) $stack->getFirstLink('sp:deployments'));
    }

    /**
     * @param Component $component
     * @return array
     */
    private function getComponentConfig(Component $component)
    {
        return json_decode($component->getExtension('com.continuouspipe.http-labs')->getConfiguration(), true);
    }

    /**
     * @param Component $component
     * @param Hal $stack
     */
    private function addMiddlewares(Component $component, Hal $stack)
    {
        $this->addBasicAuth($this->getComponentConfig($component), $stack);
        $this->addIPWhitelisting($this->getComponentConfig($component), $stack);
    }

    /**
     * @param Hal $stack
     * @param string $templateSlug
     * @param string $middlewareConfig
     */
    private function addMiddleware(Hal $stack, $templateSlug, $middlewareConfig)
    {
        $templatesLink = $this->templatesLink();
        $this->client->post(
            (string) $stack->getFirstLink('sp:middlewares'),
            [
                'json' =>
                    [
                        'template' => "$templatesLink/$templateSlug",
                        'config' => $middlewareConfig
                    ]
            ]
        );
    }

    /**
     * @param $name
     * @return mixed
     */
    private function createStack($name)
    {
        $response = $this->client->post(
            (string) $this->getProject()->getFirstLink('sp:stacks'),
            ['json' => ['name' => $name]]
        );

        return $this->client->get($response->getHeader('location'))->hal();
    }

    /**
     * @return Hal
     */
    private function fetchRoot()
    {
        return $this->client->get('/')->hal();
    }

    /**
     * @return array
     */
    private function getProjects()
    {
        $projects = $this->fetchProjects();

        if ($this->isProjectMissing($projects)) {
            $this->addProject();
            return $this->fetchProjects();
        }

        return $projects;
    }

    private function addProject()
    {
        $this->client->post($this->projectsLink(), ['json' => ['name' => 'continuous pipe', 'team' => 'inviqa']]);
    }

    /**
     * @param array $projects
     * @return bool
     */
    private function isProjectMissing(array $projects)
    {
        $projectName = function ($project) {
            return $project->getData()['name'];
        };

        $projectNames = array_map($projectName, $projects);

        return !in_array('continuous pipe', $projectNames);
    }
}