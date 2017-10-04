<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\Authenticator\UserContext;
use SimpleBus\Message\Bus\MessageBus;

class BuildFactory
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    public function fromRequest(BuildRequest $request, string $identifier = null) : Build
    {
        $build = Build::createFromRequest($request, $identifier);

        foreach ($build->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $build;
    }
}
