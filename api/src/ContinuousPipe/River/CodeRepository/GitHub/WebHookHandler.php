<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\Event\GitHub\CodePushed;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
use ContinuousPipe\River\Event\GitHub\PullRequestSynchronized;
use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Event\StatusEvent;
use GitHub\WebHook\GitHubRequest;
use SimpleBus\Message\Bus\MessageBus;

class WebHookHandler
{
    /**
     * @var CodeReferenceResolver
     */
    private $codeReferenceResolver;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var View\TideRepository
     */
    private $tideViewRepository;

    /**
     * @param CodeReferenceResolver $codeReferenceResolver
     * @param MessageBus            $eventBus
     * @param View\TideRepository   $tideViewRepository
     */
    public function __construct(
        CodeReferenceResolver $codeReferenceResolver,
        MessageBus $eventBus,
        View\TideRepository $tideViewRepository
    ) {
        $this->codeReferenceResolver = $codeReferenceResolver;
        $this->eventBus = $eventBus;
        $this->tideViewRepository = $tideViewRepository;
    }

    /**
     * @param Flow          $flow
     * @param GitHubRequest $gitHubRequest
     *
     * @return array
     */
    public function handle(Flow $flow, GitHubRequest $gitHubRequest)
    {
        $event = $gitHubRequest->getEvent();
        if ($event instanceof PushEvent) {
            $this->handlePushEvent($flow, $event);
        } elseif ($event instanceof PullRequestEvent) {
            $this->handlePullRequestEvent($flow, $event);
        } elseif ($event instanceof StatusEvent) {
            $this->handleStatusEvent($event);
        }

        return [
            'flow' => $flow,
        ];
    }

    /**
     * @param Flow      $flow
     * @param PushEvent $event
     *
     * @return \ContinuousPipe\River\View\Tide|null
     */
    private function handlePushEvent(Flow $flow, PushEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPushEvent($event);
        $this->eventBus->handle(new CodePushed($flow, $event, $codeReference));
    }

    /**
     * @param Flow             $flow
     * @param PullRequestEvent $event
     */
    private function handlePullRequestEvent(Flow $flow, PullRequestEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPullRequestEvent($event);

        if ($event->getAction() == PullRequestEvent::ACTION_OPENED) {
            $this->eventBus->handle(new PullRequestOpened($flow, $codeReference, $event));
        } elseif ($event->getAction() == PullRequestEvent::ACTION_CLOSED) {
            $this->eventBus->handle(new PullRequestClosed($flow, $codeReference, $event));
        } elseif ($event->getAction() == PullRequestEvent::ACTION_SYNCHRONIZED) {
            $this->eventBus->handle(new PullRequestSynchronized($flow, $codeReference, $event));
        }
    }

    /**
     * @param StatusEvent $event
     */
    private function handleStatusEvent(StatusEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromStatusEvent($event);
        $tides = $this->tideViewRepository->findByCodeReference($codeReference);

        foreach ($tides as $tide) {
            $this->eventBus->handle(new StatusUpdated(
                $tide->getUuid(),
                $event
            ));
        }
    }
}
