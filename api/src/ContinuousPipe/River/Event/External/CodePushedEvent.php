<?php

namespace ContinuousPipe\River\Event\External;

use ContinuousPipe\Builder\Repository;
use GitHub\WebHook\Event\PushEvent;

class CodePushedEvent
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param PushEvent $pushEvent
     *
     * @return CodePushedEvent
     */
    public static function fromGitHubPush(PushEvent $pushEvent)
    {
        $reference = $pushEvent->getReference();
        if (0 === strpos($reference, 'refs/heads/')) {
            $reference = substr($reference, strlen('refs/heads/'));
        }

        $self = new self(
            new Repository(
                $pushEvent->getRepository()->getUrl(),
                $reference
            )
        );

        return $self;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
