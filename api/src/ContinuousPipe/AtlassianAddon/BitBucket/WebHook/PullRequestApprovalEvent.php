<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use ContinuousPipe\AtlassianAddon\BitBucket\Approval;
use JMS\Serializer\Annotation as JMS;

abstract class PullRequestApprovalEvent extends PullRequestEvent
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Approval")
     *
     * @var Approval
     */
    private $approval;
}
