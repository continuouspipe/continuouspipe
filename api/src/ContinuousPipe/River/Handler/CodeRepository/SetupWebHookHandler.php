<?php

namespace ContinuousPipe\River\Handler\CodeRepository;

use ContinuousPipe\River\CodeRepository\WebHook\RepositoryWebHookManager;
use ContinuousPipe\River\Command\CodeRepository\SetupWebHookCommand;
use ContinuousPipe\River\Event\CodeRepository\WebHookConfigured;
use SimpleBus\Message\Bus\MessageBus;

class SetupWebHookHandler
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
     * @param SetupWebHookCommand $command
     */
    public function handle(SetupWebHookCommand $command)
    {
        $codeRepository = $command->getRepository();

        // FIXME It might be interesting to use the user here :)
        $this->repositoryWebHookManager->configureWebHookForRepository($codeRepository);

        $this->eventBus->handle(new WebHookConfigured($codeRepository));
    }
}
