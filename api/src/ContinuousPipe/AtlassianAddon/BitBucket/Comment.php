<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Comment
{
    /**
     * @JMS\Type("integer")
     *
     * @var integer
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
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $createdOn;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $updatedOn;
}
