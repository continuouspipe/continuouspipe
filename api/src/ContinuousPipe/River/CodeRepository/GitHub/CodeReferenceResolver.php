<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use GitHub\WebHook\Event\PushEvent;

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
        $branch = $pushEvent->getReference();
        if (0 === strpos($branch, 'refs/heads/')) {
            $branch = substr($branch, strlen('refs/heads/'));
        }

        $sha1 = $pushEvent->getHeadCommit()->getId();

        return new CodeReference(
            new GitHubCodeRepository(
                $pushEvent->getRepository()
            ),
            $sha1,
            $branch
        );
    }
}
