<?php

namespace ContinuousPipe\Pipe\Kubernetes\Event\Environment;

use ContinuousPipe\Model\Environment;
use Kubernetes\Client\NamespaceClient;
use Symfony\Component\EventDispatcher\Event;

class EnvironmentDeletionEvent extends Event
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var NamespaceClient
     */
    private $client;

    public function __construct(NamespaceClient $client, Environment $environment)
    {
        $this->client = $client;
        $this->environment = $environment;
    }

    public function getClient(): NamespaceClient
    {
        return $this->client;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
}
