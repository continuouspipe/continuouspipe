<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Filter\View\CodeReferenceView;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tide;

class ContextFactory
{
    /**
     * Create the context available in tasks' filters.
     *
     * @param Tide $tide
     *
     * @return array
     */
    public function create(Tide $tide)
    {
        $context = $tide->getContext();

        return [
            'codeReference' => CodeReferenceView::fromCodeReference($context->getCodeReference()),
            'tasks' => $this->createTasksView($tide->getTasks()->getTasks()),
        ];
    }

    /**
     * @param Task[] $tasks
     *
     * @return object
     */
    private function createTasksView(array $tasks)
    {
        $view = [];

        foreach ($tasks as $task) {
            $taskId = $task->getContext()->getTaskId();
            $view[$taskId] = $task->getExposedContext();
        }

        // Transforms the view from array to object
        return json_decode(json_encode($view));
    }
}
