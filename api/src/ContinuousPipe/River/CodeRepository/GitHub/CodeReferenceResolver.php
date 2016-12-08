<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Event\StatusEvent;
use GitHub\WebHook\Model\Repository;

class CodeReferenceResolver
{
    const EMPTY_COMMIT = '0000000000000000000000000000000000000000';

    /**
     * Create code reference from a GitHub push event.
     *
     * @param PushEvent $pushEvent
     *
     * @return CodeReference
     */
    public function fromPushEvent(PushEvent $pushEvent)
    {
        return $this->create(
            $pushEvent->getRepository(),
            $pushEvent->getReference(),
            $pushEvent->getAfter() !== self::EMPTY_COMMIT ? $pushEvent->getAfter() : $pushEvent->getBefore()
        );
    }

    /**
     * @param PullRequestEvent $event
     *
     * @return CodeReference
     */
    public function fromPullRequestEvent(PullRequestEvent $event)
    {
        $headReference = $event->getPullRequest()->getHead();

        return $this->create(
            $event->getRepository(),
            $this->isDifferentRepositories($event) ? $headReference->getLabel() : $headReference->getReference(),
            $headReference->getSha1()
        );
    }

    /**
     * Create a CodeReference from the given status event object.
     *
     * @param StatusEvent $event
     *
     * @throws \InvalidArgumentException
     *
     * @return CodeReference
     */
    public function fromStatusEvent(StatusEvent $event)
    {
        $branches = $event->getBranches();
        if (count($branches) == 0) {
            throw new \InvalidArgumentException('The status event is not related to any branch');
        }

        $branch = $branches[0];

        return $this->create(
            $event->getRepository(),
            $branch->getName(),
            $branch->getCommit()->getSha1()
        );
    }

    /**
     * @param Repository $repository
     * @param string     $branch
     * @param string     $sha1
     *
     * @return CodeReference
     */
    private function create(Repository $repository, $branch, $sha1)
    {
        if (0 === strpos($branch, 'refs/heads/')) {
            $branch = substr($branch, strlen('refs/heads/'));
        } elseif (0 === strpos($branch, 'refs/head/')) {
            $branch = substr($branch, strlen('refs/head/'));
        }

        return new CodeReference(
            GitHubCodeRepository::fromRepository($repository),
            $sha1,
            $branch
        );
    }

    /**
     * @param PullRequestEvent $event
     *
     * @return bool
     */
    private function isDifferentRepositories(PullRequestEvent $event)
    {
        return $event->getPullRequest()->getBase()->getRepository()->getId() != $event->getPullRequest()->getHead()->getRepository()->getId();
    }
}
