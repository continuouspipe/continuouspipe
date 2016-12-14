<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\Task\Task;
use LogStream\Log;
use LogStream\Node\Text;
use JMS\Serializer\Annotation as JMS;

final class TideTaskView
{
    /**
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $identifier;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $label;

    /**
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $status;

    private function __construct()
    {
    }

    public static function fromTask(Task $task) : TideTaskView
    {
        $view = new self();
        $view->identifier = $task->getIdentifier();
        $view->label = $task->getLabel();

        if ($task->isFailed()) {
            $view->status = 'failed';
        } elseif ($task->isRunning()) {
            $view->status = 'running';
        } elseif ($task->isSuccessful()) {
            $view->status = 'success';
        } elseif ($task->isPending()) {
            $view->status = 'pending';
        } elseif ($task->isSkipped()) {
            $view->status = 'skipped';
        } else {
            $view->status = 'unknown';
        }

        return $view;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
