<?php

namespace ContinuousPipe\River\Task;

use Symfony\Component\DependencyInjection\ContainerInterface;

class TaskFactoryRegistry
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
     * @return TaskFactory
     *
     * @throws TaskFactoryNotFound
     */
    public function find($name)
    {
        if (!array_key_exists($name, $this->tasksByServiceName)) {
            throw new TaskFactoryNotFound(sprintf(
                'Task "%s" is not found',
                $name
            ));
        }

        $serviceId = $this->tasksByServiceName[$name];

        return $this->container->get($serviceId);
    }
}
