<?php

namespace ContinuousPipe\River\Notifications\Events;

use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\Event\TideEvent;
use Ramsey\Uuid\UuidInterface;

class CommentedPullRequest implements TideEvent
{
    /**
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @var PullRequest
     */
    private $pullRequest;

    /**
     * @var string
     */
    private $commentIdentifier;

    public function __construct(UuidInterface $tideUuid, PullRequest $pullRequest, string $commentIdentifier)
    {
        $this->tideUuid = $tideUuid;
        $this->pullRequest = $pullRequest;
        $this->commentIdentifier = $commentIdentifier;
    }

    /**
     * @return UuidInterface
     */
    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    /**
     * @return PullRequest
     */
    public function getPullRequest(): PullRequest
    {
        return $this->pullRequest;
    }

    /**
     * @return string
     */
    public function getCommentIdentifier(): string
    {
        return $this->commentIdentifier;
    }
}
