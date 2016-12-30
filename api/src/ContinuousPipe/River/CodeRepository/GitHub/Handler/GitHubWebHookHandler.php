<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\Handler;

use ContinuousPipe\River\CodeRepository\GitHub\CodeReferenceResolver;
use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\CodeRepository\Event\BranchDeleted;
use ContinuousPipe\River\Event\GitHub\CodePushed;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
use ContinuousPipe\River\Event\GitHub\PullRequestSynchronized;
use ContinuousPipe\River\Event\GitHub\StatusUpdated;
use ContinuousPipe\River\Notifications\GitHub\CommitStatus\GitHubCommitStatusNotifier;
use ContinuousPipe\River\View;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Event\StatusEvent;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CodeReferenceResolver $codeReferenceResolver
     * @param MessageBus            $eventBus
     * @param View\TideRepository   $tideViewRepository
     * @param LoggerInterface       $logger
     */
    public function __construct(
        CodeReferenceResolver $codeReferenceResolver,
        MessageBus $eventBus,
        View\TideRepository $tideViewRepository,
        LoggerInterface $logger
    ) {
        $this->codeReferenceResolver = $codeReferenceResolver;
        $this->eventBus = $eventBus;
        $this->tideViewRepository = $tideViewRepository;
        $this->logger = $logger;
    }

    /**
     * @param HandleGitHubEvent $command
     */
    public function handle(HandleGitHubEvent $command)
    {
        $event = $command->getEvent();

        if ($event instanceof PushEvent) {
            $this->handlePushEvent($command->getFlowUuid(), $event);
        } elseif ($event instanceof PullRequestEvent) {
            $this->handlePullRequestEvent($command->getFlowUuid(), $event);
        } elseif ($event instanceof StatusEvent) {
            $this->handleStatusEvent($command->getFlowUuid(), $event);
        }
    }

    /**
     * @param UuidInterface $flowUuid
     * @param PushEvent     $event
     *
     * @return \ContinuousPipe\River\View\Tide|null
     */
    private function handlePushEvent(UuidInterface $flowUuid, PushEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPushEvent($event);

        if ($event->isDeleted()) {
            $this->eventBus->handle(new BranchDeleted($flowUuid, $codeReference));
        } elseif ($codeReference->getCommitSha() !== null) {
            $this->eventBus->handle(new CodePushed($flowUuid, $event, $codeReference));
        }
    }

    /**
     * @param UuidInterface    $flowUuid
     * @param PullRequestEvent $event
     */
    private function handlePullRequestEvent(UuidInterface $flowUuid, PullRequestEvent $event)
    {
        $codeReference = $this->codeReferenceResolver->fromPullRequestEvent($event);

        if ($event->getAction() == PullRequestEvent::ACTION_OPENED) {
            $this->eventBus->handle(new PullRequestOpened($flowUuid, $codeReference, $event));
        } elseif ($event->getAction() == PullRequestEvent::ACTION_CLOSED) {
            $this->eventBus->handle(new PullRequestClosed($flowUuid, $codeReference, $event));
        } elseif ($event->getAction() == PullRequestEvent::ACTION_SYNCHRONIZED) {
            $this->eventBus->handle(new PullRequestSynchronized($flowUuid, $codeReference, $event));
        } elseif ($event->getAction() == PullRequestEvent::ACTION_LABELED) {
            $this->eventBus->handle(new PullRequestSynchronized($flowUuid, $codeReference, $event));
        }
    }

    /**
     * @param UuidInterface $flowUuid
     * @param StatusEvent   $event
     */
    private function handleStatusEvent(UuidInterface $flowUuid, StatusEvent $event)
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

        $tides = $this->tideViewRepository->findByCodeReference($flowUuid, $codeReference);

        foreach ($tides as $tide) {
            $this->eventBus->handle(new StatusUpdated(
                $tide->getUuid(),
                $event
            ));
        }
    }
}
