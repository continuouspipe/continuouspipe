<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\Task\Task;
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
        $view->status = $task->getStatus();

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
