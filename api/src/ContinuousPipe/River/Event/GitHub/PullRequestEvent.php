<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use Ramsey\Uuid\UuidInterface;

abstract class PullRequestEvent implements CodeRepositoryEvent
{
    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @var PullRequest
     */
    private $pullRequest;

    /**
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     * @param PullRequest   $pullRequest
     */
    public function __construct(UuidInterface $flowUuid, CodeReference $codeReference, PullRequest $pullRequest)
    {
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
        $this->pullRequest = $pullRequest;
    }

    /**
     * @return PullRequest|null
     */
    public function getPullRequest()
    {
        return $this->pullRequest;
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
