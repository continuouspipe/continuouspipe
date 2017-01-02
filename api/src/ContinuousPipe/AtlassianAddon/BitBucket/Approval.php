<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Approval
{
    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\User")
     *
     * @var User
     */
    private $user;
}
