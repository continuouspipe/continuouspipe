<?php

namespace ContinuousPipe\River\EventListener\Flow;

use ContinuousPipe\River\CodeRepository\WebHook\RepositoryWebHookManager;
use ContinuousPipe\River\Event\FlowCreated;

class ConfigureWebhookAtCreationListener
{
    /**
     * @var RepositoryWebHookManager
     */
    private $repositoryWebHookManager;

    /**
     * @param RepositoryWebHookManager $repositoryWebHookManager
     */
    public function __construct(RepositoryWebHookManager $repositoryWebHookManager)
    {
        $this->repositoryWebHookManager = $repositoryWebHookManager;
    }

    /**
     * @param FlowCreated $event
     */
    public function notify(FlowCreated $event)
    {
        $flow = $event->getFlow();

        $this->repositoryWebHookManager->configureWebHookForFlow($flow);
    }
}
