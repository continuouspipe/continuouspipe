<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

use JMS\Serializer\Annotation as JMS;

class PushDetails
{
    /**
     * @JMS\Type("array<ContinuousPipe\AtlassianAddon\BitBucket\WebHook\Change>")
     *
     * @var Change[]
     */
    private $changes;

    /**
     * @return Change[]
     */
    public function getChanges(): array
    {
        return $this->changes;
    }
}
