<?php

namespace ContinuousPipe\River\Task;

use Symfony\Component\DependencyInjection\ContainerInterface;

class TaskRegistry
{
    /**
     * @var string[]
     */
    private $tasksByServiceName;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @param string $serviceId
     */
    public function register($name, $serviceId)
    {
        $this->tasksByServiceName[$name] = $serviceId;
    }

    /**
     * @param string $name
     *
     * @return Task
     *
     * @throws TaskNotFound
     */
    public function find($name)
    {
        if (!array_key_exists($name, $this->tasksByServiceName)) {
            throw new TaskNotFound(sprintf(
                'Task "%s" is not found',
                $name
            ));
        }

        $serviceId = $this->tasksByServiceName[$name];

        return $this->container->get($serviceId);
    }
}
