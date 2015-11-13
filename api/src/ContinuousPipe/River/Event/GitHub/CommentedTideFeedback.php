<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class CommentedTideFeedback implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var int
     */
    private $commentId;

    /**
     * @param Uuid $tideUuid
     * @param int $commentId
     */
    public function __construct(Uuid $tideUuid, $commentId)
    {
        $this->tideUuid = $tideUuid;
        $this->commentId = $commentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return int
     */
    public function getCommentId()
    {
        return $this->commentId;
    }
}