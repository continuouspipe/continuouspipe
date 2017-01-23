<?php

namespace ContinuousPipe\UserActivity;

use ContinuousPipe\River\CodeRepository\Event\CodePushed;

class DispatchFromCodeRepositoryEvents
{
    private $userActivityDispatcher;

    public function __construct(UserActivityDispatcher $userActivityDispatcher)
    {
        $this->userActivityDispatcher = $userActivityDispatcher;
    }

    public function notify(CodePushed $event)
    {
        foreach ($event->getUsers() as $user) {
            $this->userActivityDispatcher->dispatch(new UserActivity(
                $event->getFlowUuid(),
                UserActivity::TYPE_PUSH,
                $user,
                new \DateTime()
            ));
        }
    }
}
