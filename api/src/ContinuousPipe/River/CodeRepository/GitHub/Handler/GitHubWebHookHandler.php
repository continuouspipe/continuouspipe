<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Handler;

use ContinuousPipe\River\CodeRepository\GitHub\CodeReferenceResolver;
use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\Event\GitHub\BranchDeleted;
use ContinuousPipe\River\Event\GitHub\CodePushed;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
use ContinuousPipe\River\Event\GitHub\PullRequestSynchronized;
use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Notifications\GitHub\CommitStatus\GitHubCommitStatusNotifier;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Event\StatusEvent;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CodeReferenceResolver $codeReferenceResolver
     * @param MessageBus            $eventBus
     * @param View\TideRepository   $tideViewRepository
     * @param FlowRepository        $flowRepository
     * @param LoggerInterface       $logger
     */
    public function __construct(
        CodeReferenceResolver $codeReferenceResolver,
        MessageBus $eventBus,
        View\TideRepository $tideViewRepository,
        FlowRepository $flowRepository,
        LoggerInterface $logger
    ) {
        $this->codeReferenceResolver = $codeReferenceResolver;
        $this->eventBus = $eventBus;
        $this->tideViewRepository = $tideViewRepository;
        $this->flowRepository = $flowRepository;
        $this->logger = $logger;
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
            $this->handleStatusEvent($flow, $event);
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
     * @param Flow        $flow
     * @param StatusEvent $event
     */
    private function handleStatusEvent(Flow $flow, StatusEvent $event)
    {
        if ($event->getContext() == GitHubCommitStatusNotifier::GITHUB_CONTEXT) {
            return;
        }

        try {
            $codeReference = $this->codeReferenceResolver->fromStatusEvent($event);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage(), [
                'event' => $event,
            ]);

            return;
        }

        $tides = $this->tideViewRepository->findByCodeReference($flow->getUuid(), $codeReference);

        foreach ($tides as $tide) {
            $this->eventBus->handle(new StatusUpdated(
                $tide->getUuid(),
                $event
            ));
        }
    }
}
