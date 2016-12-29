<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use JMS\Serializer\Annotation as JMS;

class Push extends WebHookEvent
{
    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PushDetails")
     *
     * @var PushDetails
     */
    private $push;

    /**
     * @return PushDetails
     */
    public function getPushDetails(): PushDetails
    {
        return $this->push;
    }
}
