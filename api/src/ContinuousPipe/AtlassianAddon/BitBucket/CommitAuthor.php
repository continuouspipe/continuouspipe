<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class CommitAuthor
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $raw;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\User")
     *
     * @var User
     */
    private $user;
}
