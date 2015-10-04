<?php

namespace ContinuousPipe\River\Filter\TaskRunner;

use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Task\TaskSkipped;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
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
     * @param TaskRunner     $taskRunner
     * @param ContextFactory $contextFactory
     */
    public function __construct(TaskRunner $taskRunner, ContextFactory $contextFactory)
    {
        $this->taskRunner = $taskRunner;
        $this->contextFactory = $contextFactory;
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
            if ($this->shouldSkipTask($taskConfiguration, $tide)) {
                return $task->apply(new TaskSkipped($tide->getUuid(), $taskId));
            }
        } catch (TideConfigurationException $e) {
            throw new TaskRunnerException($e->getMessage(), $e->getCode(), $e, $task);
        }

        return $this->taskRunner->run($tide, $task);
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
            ));
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
