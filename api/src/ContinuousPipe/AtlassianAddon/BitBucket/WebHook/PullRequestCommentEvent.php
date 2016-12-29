<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use ContinuousPipe\AtlassianAddon\BitBucket\Comment;
use JMS\Serializer\Annotation as JMS;

abstract class PullRequestCommentEvent extends PullRequestEvent
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Comment")
     *
     * @var Comment
     */
    private $comment;
}
