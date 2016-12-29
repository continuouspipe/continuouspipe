<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use ContinuousPipe\AtlassianAddon\BitBucket\CommitStatus;
use JMS\Serializer\Annotation as JMS;

class BuildStatusCreated extends WebHookEvent
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\CommitStatus")
     * @JMS\SerializedName("commit_status")
     *
     * @var CommitStatus
     */
    private $commitStatus;
}
