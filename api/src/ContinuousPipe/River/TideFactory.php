<?php

namespace ContinuousPipe\River;

use LogStream\Log;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class TideFactory
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $eventBus
     */
    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param Uuid          $uuid
     * @param Flow          $flow
     * @param CodeReference $codeReference
     * @param Log           $log
     *
     * @return Tide
     */
    public function create(Uuid $uuid, Flow $flow, CodeReference $codeReference, Log $log)
    {
        $tide = Tide::create($uuid, $flow, $codeReference, $log);

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $tide;
    }
}
