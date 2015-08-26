<?php

namespace ContinuousPipe\Pipe\Tests\Adapter\Fake;

use ContinuousPipe\Adapter\EnvironmentClient;
use SimpleBus\Message\Bus\MessageBus;

class FakeEnvironmentClient implements EnvironmentClient
{
    /**
     * @var array
     */
    private $createdOrUpdated = [];

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
        return $this->createdOrUpdated;
    }
}
