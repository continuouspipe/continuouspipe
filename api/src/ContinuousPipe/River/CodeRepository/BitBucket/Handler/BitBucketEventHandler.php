<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Handler;

use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\Change;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\Push;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\WebHookEvent;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\Command\HandleBitBucketEvent;
use ContinuousPipe\River\CodeRepository\Event\CodePushed;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class BitBucketEventHandler
{
    private $eventBus;
    private $logger;

    public function __construct(MessageBus $eventBus, LoggerInterface $logger)
    {
        $this->eventBus = $eventBus;
        $this->logger = $logger;
    }

    public function handle(HandleBitBucketEvent $command)
    {
        $event = $command->getEvent();

        if ($event instanceof Push) {
            foreach ($event->getPushDetails()->getChanges() as $change) {
                $this->handleChange($command, $event, $change);
            }
        } else {
            $this->logger->warning('Event of type {type} was not handled', [
                'type' => get_class($event),
            ]);
        }
    }

    /**
     * @param HandleBitBucketEvent $command
     * @param WebHookEvent         $event
     * @param Change               $change
     */
    private function handleChange(HandleBitBucketEvent $command, WebHookEvent $event, Change $change)
    {
        if (null !== ($reference = $change->getNew())) {
            $this->eventBus->handle(new CodePushed(
                $command->getFlowUuid(),
                new CodeReference(
                    BitBucketCodeRepository::fromBitBucketRepository($event->getRepository()),
                    $reference->getTarget()->getHash(),
                    $reference->getName()
                )
            ));
        }
    }
}
