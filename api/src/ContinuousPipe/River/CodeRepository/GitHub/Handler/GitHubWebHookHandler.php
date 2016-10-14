<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Handler;

use ContinuousPipe\River\CodeRepository\GitHub\CodeReferenceResolver;
use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeStatusUpdater;
use ContinuousPipe\River\Event\GitHub\BranchDeleted;
use ContinuousPipe\River\Event\GitHub\CodePushed;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
use ContinuousPipe\River\Event\GitHub\PullRequestSynchronized;
use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Event\StatusEvent;
use SimpleBus\Message\Bus\MessageBus;

class GitHubWebHookHandler
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
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param CodeReferenceResolver $codeReferenceResolver
     * @param MessageBus            $eventBus
     * @param View\TideRepository   $tideViewRepository
     * @param FlowRepository        $flowRepository
     */
    public function __construct(
        CodeReferenceResolver $codeReferenceResolver,
        MessageBus $eventBus,
        View\TideRepository $tideViewRepository,
        FlowRepository $flowRepository
    ) {
        $this->codeReferenceResolver = $codeReferenceResolver;
        $this->eventBus = $eventBus;
        $this->tideViewRepository = $tideViewRepository;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @param HandleGitHubEvent $command
     */
    public function handle(HandleGitHubEvent $command)
    {
        $event = $command->getEvent();
        $flow = $this->flowRepository->find($command->getFlowUuid());

        if ($event instanceof PushEvent) {
            $this->handlePushEvent($flow, $event);
        } elseif ($event instanceof PullRequestEvent) {
            $this->handlePullRequestEvent($flow, $event);
        } elseif ($event instanceof StatusEvent) {
            $this->handleStatusEvent($event);
        }
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

        if ($event->isDeleted()) {
            $this->eventBus->handle(new BranchDeleted($flow, $codeReference));
        } elseif ($codeReference->getCommitSha() !== null) {
            $this->eventBus->handle(new CodePushed($flow, $event, $codeReference));
        }
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
        } elseif ($event->getAction() == PullRequestEvent::ACTION_LABELED) {
            $this->eventBus->handle(new PullRequestSynchronized($flow, $codeReference, $event));
        }
    }

    /**
     * @param StatusEvent $event
     */
    private function handleStatusEvent(StatusEvent $event)
    {
        if ($event->getContext() == GitHubCodeStatusUpdater::GITHUB_CONTEXT) {
            return;
        }

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
