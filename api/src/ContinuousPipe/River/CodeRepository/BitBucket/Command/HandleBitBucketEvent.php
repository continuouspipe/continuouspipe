<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Command;

use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\WebHookEvent;
use Ramsey\Uuid\UuidInterface;

class HandleBitBucketEvent
{
    private $flowUuid;
    private $event;

    public function __construct(UuidInterface $flowUuid, WebHookEvent $event)
    {
        $this->flowUuid = $flowUuid;
        $this->event = $event;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }

    public function getEvent(): WebHookEvent
    {
        return $this->event;
    }
}
