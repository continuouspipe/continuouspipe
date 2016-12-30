<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use JMS\Serializer\Annotation as JMS;

abstract class BranchEvent extends WebHookEvent
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $branch;
}
