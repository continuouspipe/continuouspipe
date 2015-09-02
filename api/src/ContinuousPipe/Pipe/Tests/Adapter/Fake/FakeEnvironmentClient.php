<?php

namespace ContinuousPipe\Pipe\Tests\Adapter\Fake;

use ContinuousPipe\Adapter\EnvironmentClient;
use ContinuousPipe\Adapter\EnvironmentNotFound;
use ContinuousPipe\Model\Environment;
use SimpleBus\Message\Bus\MessageBus;

class FakeEnvironmentClient implements EnvironmentClient
{
    /**
     * @var Environment[]
     */
    private $environments = [];

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->environments;
    }

    /**
     * @param Environment $environment
     */
    public function add(Environment $environment)
    {
        $this->environments[$environment->getIdentifier()] = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier)
    {
        if (!array_key_exists($identifier, $this->environments)) {
            throw new EnvironmentNotFound(sprintf(
                'Environment with identifier "%s" is not found',
                $identifier
            ));
        }

        return $this->environments[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Environment $environment)
    {
        array_splice(
            $this->environments,
            array_search($environment->getIdentifier(), array_keys($this->environments)),
            1
        );
    }
}
