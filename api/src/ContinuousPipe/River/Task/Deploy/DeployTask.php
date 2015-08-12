<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\TideContext;
use SimpleBus\Message\Bus\MessageBus;

class DeployTask extends EventDrivenTask
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param MessageBus $commandBus
     */
    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param TideContext $context
     */
    public function start(TideContext $context)
    {
        $this->commandBus->handle(new StartDeploymentCommand($context->getTideUuid(), new DeployContext($context)));
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return 1 <= $this->numberOfEventsOfType(DeploymentSuccessful::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return 0 < $this->numberOfEventsOfType(DeploymentFailed::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return 0 === $this->numberOfEventsOfType(DeploymentStarted::class);
    }
}
