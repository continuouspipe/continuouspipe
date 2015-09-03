<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow\Task;
use Rhumsaa\Uuid\Uuid;

class Flow
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var CodeRepository
     */
    private $repository;

    /**
     * @var Task[]
     */
    private $tasks;

    /**
     * @param \ContinuousPipe\River\Flow $flow
     *
     * @return Flow
     */
    public static function fromFlow(\ContinuousPipe\River\Flow $flow)
    {
        $flowContext = $flow->getContext();

        $view = new self();
        $view->uuid = $flowContext->getFlowUuid();
        $view->repository = $flowContext->getCodeRepository();
        $view->tasks = $flow->getTasks();

        return $view;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return \ContinuousPipe\River\Flow\Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
