<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\Event\GitHub\CodePushed;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Event\StatusEvent;
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
     * @var View\TideRepository
     */
    private $tideViewRepository;

    /**
     * @param TideFactory           $tideFactory
     * @param CodeReferenceResolver $codeReferenceResolver
     * @param MessageBus            $eventBus
     * @param View\TideRepository   $tideViewRepository
     */
    public function __construct(
        TideFactory $tideFactory,
        CodeReferenceResolver $codeReferenceResolver,
        MessageBus $eventBus,
        View\TideRepository $tideViewRepository
    ) {
        $this->tideFactory = $tideFactory;
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
            if ($tide = $this->handlePushEvent($flow, $event)) {
                return $tide;
            }
        } elseif ($event instanceof PullRequestEvent) {
            $this->handlePullRequestEvent($event);
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
        $tide = $this->tideFactory->createFromCodeReference($flow, $codeReference);
        $tide->apply(new CodePushed($tide->getUuid(), $event));

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $this->tideViewRepository->find($tide->getUuid());
    }

    /**
     * @param PullRequestEvent $event
     */
    private function handlePullRequestEvent(PullRequestEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPullRequestEvent($event);

        if ($event->getAction() == PullRequestEvent::ACTION_OPENED) {
            $this->eventBus->handle(new PullRequestOpened($event, $codeReference));
        } elseif ($event->getAction() == PullRequestEvent::ACTION_CLOSED) {
            $this->eventBus->handle(new PullRequestClosed($event, $codeReference));
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
