<?php

namespace ContinuousPipe\Pipe\Handler;

use ContinuousPipe\Pipe\Command\DeploymentCommand;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;

/**
 * This handler is in charge of "redirecting" the handling of this deployment command to
 * the good handler, based on the given provider.
 */
class DeploymentCommandsHandler
{
    /**
     * @var DeploymentHandler[][]
     */
    private $handlers = [];

    /**
     * Register a new command handler.
     *
     * @param string            $commandClass
     * @param DeploymentHandler $handler
     */
    public function register($commandClass, DeploymentHandler $handler)
    {
        if (!array_key_exists($commandClass, $this->handlers)) {
            $this->handlers[$commandClass] = [];
        }

        $this->handlers[$commandClass][] = $handler;
    }

    /**
     * @param DeploymentCommand $command
     */
    public function handle(DeploymentCommand $command)
    {
        foreach ($this->getHandlers($command) as $handler) {
            if ($handler->supports($command->getContext())) {
                $handler->handle($command);
            }
        }
    }

    /**
     * @param DeploymentCommand $command
     *
     * @return DeploymentHandler[]
     */
    private function getHandlers(DeploymentCommand $command)
    {
        $className = get_class($command);
        if (!array_key_exists($className, $this->handlers)) {
            return [];
        }

        return $this->handlers[$className];
    }
}
