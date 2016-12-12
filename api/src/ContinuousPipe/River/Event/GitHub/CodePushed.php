<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use GitHub\WebHook\Event\PushEvent;
use Ramsey\Uuid\UuidInterface;

class CodePushed implements CodeRepositoryEvent
{
    /**
     * @var PushEvent
     */
    private $gitHubEvent;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @param UuidInterface $flowUuid
     * @param PushEvent     $gitHubEvent
     * @param CodeReference $codeReference
     */
    public function __construct(UuidInterface $flowUuid, PushEvent $gitHubEvent, CodeReference $codeReference)
    {
        $this->gitHubEvent = $gitHubEvent;
        $this->codeReference = $codeReference;
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return PushEvent
     */
    public function getGitHubEvent()
    {
        return $this->gitHubEvent;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }
}
