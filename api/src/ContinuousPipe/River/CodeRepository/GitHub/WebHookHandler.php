<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\TideFactory;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\GitHubRequest;
use SimpleBus\Message\Bus\MessageBus;

class WebHookHandler
{
    /**
     * @var TideFactory
     */
    private $tideFactory;

    /**
     * @var CodeReferenceResolver
     */
    private $codeReferenceResolver;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param TideFactory           $tideFactory
     * @param CodeReferenceResolver $codeReferenceResolver
     * @param MessageBus            $eventBus
     */
    public function __construct(TideFactory $tideFactory, CodeReferenceResolver $codeReferenceResolver, MessageBus $eventBus)
    {
        $this->tideFactory = $tideFactory;
        $this->codeReferenceResolver = $codeReferenceResolver;
        $this->eventBus = $eventBus;
    }

    /**
     * @param Flow          $flow
     * @param GitHubRequest $gitHubRequest
     *
     * @return \ContinuousPipe\River\Tide|null
     */
    public function handle(Flow $flow, GitHubRequest $gitHubRequest)
    {
        $event = $gitHubRequest->getEvent();
        if ($event instanceof PushEvent) {
            return $this->handlePushEvent($flow, $event);
        }

        return;
    }

    /**
     * @param Flow      $flow
     * @param PushEvent $event
     *
     * @return \ContinuousPipe\River\Tide
     */
    private function handlePushEvent(Flow $flow, PushEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPushEvent($event);
        $tide = $this->tideFactory->createFromCodeReference($flow, $codeReference);

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $tide;
    }
}
