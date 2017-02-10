<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Comment
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Comment")
     *
     * @var Comment
     */
    private $parent;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\CommentContent")
     *
     * @var CommentContent
     */
    private $content;

    /**
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uO'>")
     *
     * @var \DateTimeInterface
     */
    private $createdOn;

    /**
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uO'>")
     *
     * @var \DateTimeInterface
     */
    private $updatedOn;
}
