<?php

namespace ContinuousPipe\River\Filter\TaskRunner;

use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Task\TaskSkipped;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class FilterDecorator implements TaskRunner
{
    /**
     * @var TaskRunner
     */
    private $taskRunner;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param TaskRunner     $taskRunner
     * @param ContextFactory $contextFactory
     * @param MessageBus     $eventBus
     */
    public function __construct(TaskRunner $taskRunner, ContextFactory $contextFactory, MessageBus $eventBus)
    {
        $this->taskRunner = $taskRunner;
        $this->contextFactory = $contextFactory;
        $this->eventBus = $eventBus;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        $configuration = $tide->getContext()->getConfiguration();
        $taskId = $task->getContext()->getTaskId();
        $taskConfiguration = $configuration['tasks'][$taskId];

        try {
            $shouldBeSkipped = $this->shouldSkipTask($taskConfiguration, $tide);
        } catch (TideConfigurationException $e) {
            throw new TaskRunnerException($e->getMessage(), $e->getCode(), $e, $task);
        }

        if ($shouldBeSkipped) {
            $this->eventBus->handle(new TaskSkipped($tide->getUuid(), $task->getContext()));
        } else {
            $this->taskRunner->run($tide, $task);
        }
    }

    /**
     * @param array $configuration
     * @param Tide  $tide
     *
     * @return bool
     *
     * @throws TideConfigurationException
     */
    private function shouldSkipTask(array $configuration, Tide $tide)
    {
        if (!isset($configuration['filter'])) {
            return false;
        }

        return !$this->testFilter($configuration['filter'], $this->contextFactory->create($tide));
    }

    /**
     * @param array $filter
     * @param array $context
     *
     * @return bool
     *
     * @throws TideConfigurationException
     */
    private function testFilter(array $filter, array $context)
    {
        $expression = $filter['expression'];
        $language = new ExpressionLanguage();

        try {
            $evaluated = $language->evaluate($expression, $context);
        } catch (SyntaxError $e) {
            throw new TideConfigurationException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $expression,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }

        if (!is_bool($evaluated)) {
            throw new TideConfigurationException(sprintf(
                'Expression "%s" is not valid as it do not return a boolean',
                $expression
            ));
        }

        return $evaluated;
    }
}
