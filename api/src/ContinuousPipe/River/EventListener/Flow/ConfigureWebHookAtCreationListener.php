<?php

namespace ContinuousPipe\River\EventListener\Flow;

use ContinuousPipe\River\CodeRepository\WebHook\RepositoryWebHookManager;
use ContinuousPipe\River\Event\BeforeFlowSave;

class ConfigureWebHookAtCreationListener
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
     * @param BeforeFlowSave $event
     */
    public function notify(BeforeFlowSave $event)
    {
        $flow = $event->getFlow();

        $this->repositoryWebHookManager->configureWebHookForFlow($flow);
    }
}
