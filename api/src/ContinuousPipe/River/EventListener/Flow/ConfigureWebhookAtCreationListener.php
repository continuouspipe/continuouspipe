<?php

namespace ContinuousPipe\River\EventListener\Flow;

use ContinuousPipe\River\CodeRepository\WebHook\RepositoryWebHookManager;
use ContinuousPipe\River\Event\CodeRepository\WebHookConfigured;
use ContinuousPipe\River\Event\FlowCreated;
use SimpleBus\Message\Bus\MessageBus;

class ConfigureWebHookAtCreationListener
{
    /**
     * @var RepositoryWebHookManager
     */
    private $repositoryWebHookManager;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param RepositoryWebHookManager $repositoryWebHookManager
     * @param MessageBus               $eventBus
     */
    public function __construct(RepositoryWebHookManager $repositoryWebHookManager, MessageBus $eventBus)
    {
        $this->repositoryWebHookManager = $repositoryWebHookManager;
        $this->eventBus = $eventBus;
    }

    /**
     * @param FlowCreated $event
     */
    public function notify(FlowCreated $event)
    {
        $flow = $event->getFlow();

        $this->repositoryWebHookManager->configureWebHookForFlow($flow);
        $this->eventBus->handle(new WebHookConfigured($flow->getRepository()));
    }
}
