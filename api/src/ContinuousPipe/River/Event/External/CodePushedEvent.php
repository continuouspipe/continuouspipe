<?php

namespace ContinuousPipe\River\Event\External;

use ContinuousPipe\Builder\Repository;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use GitHub\WebHook\Event\PushEvent;
use Rhumsaa\Uuid\Uuid;

class CodePushedEvent
{
    /**
     * @var Uuid
     */
    private $uuid;
    /**
     * @var CodeRepository
     */
    private $repository;
    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @param Uuid           $uuid
     * @param CodeRepository $repository
     * @param CodeReference  $codeReference
     */
    private function __construct(Uuid $uuid, CodeRepository $repository, CodeReference $codeReference)
    {
        $this->repository = $repository;
        $this->codeReference = $codeReference;
        $this->uuid = $uuid;
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
            Uuid::uuid1(),
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

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
