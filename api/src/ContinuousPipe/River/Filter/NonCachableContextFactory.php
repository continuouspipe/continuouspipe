<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use Ramsey\Uuid\UuidInterface;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Filter\View\TaskListView;

class NonCachableContextFactory implements ContextFactory
{
    /**
     * @var ContextFactory
     */
    private $decoratedContext;

    public function __construct(ContextFactory $decoratedContext)
    {
        $this->decoratedContext = $decoratedContext;
    }

    /**
     * {@inheritdoc}
     */
    public function create(UuidInterface $flowUuid, CodeReference $codeReference, Tide $tide = null)
    {
        $context = $this->decoratedContext->create($flowUuid, $codeReference);

        if (null !== $tide) {
            $context['tide'] = new ArrayObject([
                'uuid' => (string) $tide->getUuid(),
            ]);

            $context['tasks'] = $this->createTasksView($tide->getTasks()->getTasks());
        }

        return $context;
    }

    /**
     * @param Task[] $tasks
     *
     * @return object
     */
    private function createTasksView(array $tasks)
    {
        $view = new TaskListView();

        foreach ($tasks as $task) {
            $taskId = $task->getIdentifier();

            $view->add($taskId, $task->getExposedContext());
        }

        return $view;
    }

}
