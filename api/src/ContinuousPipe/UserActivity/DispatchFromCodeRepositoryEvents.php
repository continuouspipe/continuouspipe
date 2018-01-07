<?php

namespace ContinuousPipe\UserActivity;

use ContinuousPipe\River\CodeRepository\Event\CodePushed;
use ContinuousPipe\River\Repository\FlowRepository;

class DispatchFromCodeRepositoryEvents
{
    private $userActivityDispatcher;
    private $flowRepository;

    public function __construct(
        UserActivityDispatcher $userActivityDispatcher,
        FlowRepository $flowRepository
    ) {
        $this->userActivityDispatcher = $userActivityDispatcher;
        $this->flowRepository = $flowRepository;
    }

    public function notify(CodePushed $event)
    {
        $flow = $this->flowRepository->find($event->getFlowUuid());

        foreach ($event->getUsers() as $user) {
            $this->userActivityDispatcher->dispatch(new UserActivity(
                $flow->getTeam()->getSlug(),
                $event->getFlowUuid(),
                UserActivity::TYPE_PUSH,
                $user,
                new \DateTime()
            ));
        }
    }
}
