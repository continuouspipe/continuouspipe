<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Handler;

use ContinuousPipe\AtlassianAddon\BitBucket\Reference;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\Change;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestCreated;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\Push;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\WebHookEvent;
use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest as BitBucketPullRequest;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\Command\HandleBitBucketEvent;
use ContinuousPipe\River\CodeRepository\CodeRepositoryUser;
use ContinuousPipe\River\CodeRepository\Event\BranchDeleted;
use ContinuousPipe\River\CodeRepository\Event\CodePushed;
use ContinuousPipe\River\CodeRepository\Event\PullRequestOpened;
use ContinuousPipe\River\CodeRepository\PullRequest;
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
        } elseif ($event instanceof PullRequestCreated) {
            $pullRequest = $event->getPullRequest();

            $this->eventBus->handle(new PullRequestOpened(
                $command->getFlowUuid(),
                $this->createPullRequestCodeReference($event, $pullRequest),
                new PullRequest(
                    $pullRequest->getId(),
                    $pullRequest->getTitle()
                )
            ));
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
                $this->createCodeReference($event, $reference),
                $this->resolveUsers($change)
            ));
        } elseif (null !== ($reference = $change->getOld())) {
            $this->eventBus->handle(new BranchDeleted(
                $command->getFlowUuid(),
                $this->createCodeReference($event, $reference)
            ));
        }
    }

    /**
     * @param WebHookEvent $event
     * @param Reference    $reference
     *
     * @return CodeReference
     */
    private function createCodeReference(WebHookEvent $event, Reference $reference): CodeReference
    {
        return new CodeReference(
            BitBucketCodeRepository::fromBitBucketRepository($event->getRepository()),
            $reference->getTarget()->getHash(),
            $reference->getName()
        );
    }

    private function createPullRequestCodeReference(WebHookEvent $event, BitBucketPullRequest $pullRequest)
    {
        return new CodeReference(
            BitBucketCodeRepository::fromBitBucketRepository($event->getRepository()),
            $pullRequest->getSource()->getCommit()->getHash(),
            $pullRequest->getSource()->getBranch()->getName()
        );
    }

    /**
     * @param Change $change
     *
     * @return CodeRepositoryUser[]
     */
    private function resolveUsers(Change $change)
    {
        $users = [];

        foreach ($change->getCommits() as $commit) {
            if (null === ($commitUser = $commit->getAuthor()->getUser())) {
                continue;
            }

            $user = new CodeRepositoryUser($commitUser->getUsername(), null, $commitUser->getDisplayName());
            if (!in_array($user, $users)) {
                $users[] = $user;
            }
        }

        return $users;
    }
}
