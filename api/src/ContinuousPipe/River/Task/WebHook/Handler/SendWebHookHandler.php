<?php

namespace ContinuousPipe\River\Task\WebHook\Handler;

use ContinuousPipe\River\Task\WebHook\Command\SendWebHook;
use ContinuousPipe\River\Task\WebHook\Event\WebHookFailed;
use ContinuousPipe\River\Task\WebHook\Event\WebHookSent;
use ContinuousPipe\River\WebHook\WebHookClient;
use ContinuousPipe\River\WebHook\WebHookException;
use SimpleBus\Message\Bus\MessageBus;

class SendWebHookHandler
{
    /**
     * @var WebHookClient
     */
    private $webHookClient;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param WebHookClient $webHookClient
     * @param MessageBus    $eventBus
     */
    public function __construct(WebHookClient $webHookClient, MessageBus $eventBus)
    {
        $this->webHookClient = $webHookClient;
        $this->eventBus = $eventBus;
    }

    /**
     * @param SendWebHook $command
     */
    public function handle(SendWebHook $command)
    {
        try {
            $this->webHookClient->send($command->getWebHook());

            $this->eventBus->handle(new WebHookSent(
                $command->getTideUuid(),
                $command->getTaskId(),
                $command->getWebHook()
            ));
        } catch (WebHookException $e) {
            $this->eventBus->handle(new WebHookFailed(
                $command->getTideUuid(),
                $command->getTaskId(),
                $command->getWebHook(),
                $e
            ));
        }
    }
}
