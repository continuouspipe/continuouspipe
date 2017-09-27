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

    /**
     * @var UserContext
     */
    private $userContext;

    public function __construct(MessageBus $eventBus, UserContext $userContext)
    {
        $this->eventBus = $eventBus;
        $this->userContext = $userContext;
    }

    public function fromRequest(BuildRequest $request, string $identifier = null) : Build
    {
        $user = $this->userContext->getCurrent();

        $build = Build::createFromRequest($request, $user, $identifier);

        foreach ($build->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $build;
    }
}
