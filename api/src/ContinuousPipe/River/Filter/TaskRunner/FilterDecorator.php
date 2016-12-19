<?php

namespace ContinuousPipe\River\Filter\TaskRunner;

use ContinuousPipe\River\Filter\FilterEvaluator;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Task\TaskSkipped;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

class FilterDecorator implements TaskRunner
{
    /**
     * @var TaskRunner
     */
    private $taskRunner;

    /**
     * @var FilterEvaluator
     */
    private $filterEvaluator;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TaskRunner      $taskRunner
     * @param FilterEvaluator $filterEvaluator
     * @param MessageBus      $eventBus
     * @param LoggerInterface $logger
     */
    public function __construct(TaskRunner $taskRunner, FilterEvaluator $filterEvaluator, MessageBus $eventBus, LoggerInterface $logger)
    {
        $this->taskRunner = $taskRunner;
        $this->filterEvaluator = $filterEvaluator;
        $this->eventBus = $eventBus;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        try {
            $taskConfiguration = $this->getTaskConfiguration($tide, $task->getIdentifier());
            $shouldBeSkipped = $this->shouldSkipTask($taskConfiguration, $tide);
        } catch (TideConfigurationException $e) {
            $this->logger->error('Task configuration exception', [
                'exception' => $e,
            ]);

            throw new TaskRunnerException($e->getMessage(), $e->getCode(), $e, $task);
        }

        if ($shouldBeSkipped) {
            $this->eventBus->handle(new TaskSkipped($tide->getUuid(), $task->getIdentifier(), $task->getLogIdentifier()));
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

        return !$this->filterEvaluator->evaluates($tide, $configuration['filter']);
    }

    /**
     * @param Tide   $tide
     * @param string $identifier
     *
     * @throws TideConfigurationException
     *
     * @return array
     */
    private function getTaskConfiguration(Tide $tide, string $identifier)
    {
        $configuration = $tide->getContext()->getConfiguration();

        foreach ($configuration['tasks'] as $key => $task) {
            if (isset($task['identifier']) && $task['identifier'] == $identifier || $key == $identifier) {
                return $task;
            }
        }

        throw new TideConfigurationException(sprintf('Configuration of task "%s" cannot be found', $identifier));
    }
}
