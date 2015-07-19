<?php

namespace ContinuousPipe\River\Event\External;

use ContinuousPipe\Builder\Repository;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use GitHub\WebHook\Event\PushEvent;

class CodePushedEvent
{
    /**
     * @var CodeRepository
     */
    private $repository;
    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @param CodeRepository $repository
     * @param CodeReference $codeReference
     */
    public function __construct(CodeRepository $repository, CodeReference $codeReference)
    {
        $this->repository = $repository;
        $this->codeReference = $codeReference;
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
            new CodeRepository\GitHub\GitHubCodeRepository(
                $pushEvent->getRepository()
            ),
            new CodeReference($reference)
        );

        return $self;
    }

    /**
     * @return CodeRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }
}
