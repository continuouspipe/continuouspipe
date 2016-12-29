<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest;
use JMS\Serializer\Annotation as JMS;

abstract class PullRequestEvent extends WebHookEvent
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\PullRequest")
     * @JMS\SerializedName("pullrequest")
     *
     * @var PullRequest
     */
    private $pullRequest;
}
