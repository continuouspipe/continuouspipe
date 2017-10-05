<?php

namespace ContinuousPipe\Pipe\EventBus\Middleware;

use ContinuousPipe\Pipe\DeploymentRepository;
use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\EventBus\EventStore;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class ApplyDeploymentEventsMiddleware implements MessageBusMiddleware
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @param DeploymentRepository $deploymentRepository
     * @param EventStore           $eventStore
     */
    public function __construct(DeploymentRepository $deploymentRepository, EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
        $this->deploymentRepository = $deploymentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        $next($message);

        if ($message instanceof DeploymentEvent) {
            $this->eventStore->add($message);
        }
    }
}
