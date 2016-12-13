<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\Task\Task;
use LogStream\Log;
use LogStream\Node\Text;

final class TideTaskView
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $status;

    private function __construct()
    {
    }

    public static function fromTask(Task $task) : TideTaskView
    {
        $view = new self();
        $view->identifier = $task->getContext()->getTaskId();

        $log = $task->getContext()->getTaskLog();
        if ($log instanceof Log) {
            $node = $log->getNode();
            if ($node instanceof Text) {
                $view->label = $node->getText();
            }
        }

        if (!$view->label) {
            $view->label = $view->identifier;
        }

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
