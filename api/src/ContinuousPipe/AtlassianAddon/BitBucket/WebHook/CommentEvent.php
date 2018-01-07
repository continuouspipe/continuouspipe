<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use ContinuousPipe\AtlassianAddon\BitBucket\Comment;
use ContinuousPipe\AtlassianAddon\BitBucket\Commit;
use JMS\Serializer\Annotation as JMS;

class CommentEvent extends WebHookEvent
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Comment")
     *
     * @var Comment
     */
    private $comment;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Commit")
     *
     * @var Commit
     */
    private $commit;
}
