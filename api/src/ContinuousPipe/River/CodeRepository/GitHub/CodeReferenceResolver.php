<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use GitHub\WebHook\Event\PullRequestEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Model\CommitReference;
use GitHub\WebHook\Model\Repository;

class CodeReferenceResolver
{
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
            $pushEvent->getHeadCommit()->getId()
        );
    }

    /**
     * @param PullRequestEvent $event
     * @return CodeReference
     */
    public function fromPullRequestEvent(PullRequestEvent $event)
    {
        $headReference = $event->getPullRequest()->getHead();

        return $this->create(
            $event->getRepository(),
            $headReference->getReference(),
            $headReference->getSha1()
        );
    }

    /**
     * @param Repository $repository
     * @param string $branch
     * @param string $sha1
     * @return CodeReference
     */
    private function create(Repository $repository, $branch, $sha1)
    {
        if (0 === strpos($branch, 'refs/heads/')) {
            $branch = substr($branch, strlen('refs/heads/'));
        }

        return new CodeReference(
            new GitHubCodeRepository($repository),
            $sha1,
            $branch
        );
    }
}
