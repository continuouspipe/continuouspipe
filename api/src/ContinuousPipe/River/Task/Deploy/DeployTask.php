<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\EventDrivenTask;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class DeployTask extends EventDrivenTask
{
    /**
     * Name of the task.
     *
     * @var string
     */
    const NAME = 'deploy';

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var DeployContext
     */
    private $context;

    /**
     * @param MessageBus    $commandBus
     * @param LoggerFactory $loggerFactory
     * @param DeployContext $context
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory, DeployContext $context)
    {
        parent::__construct();

        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $logger = $this->loggerFactory->from($this->context->getTideLog());
        $log = $logger->append(new Text('Deploying environment'));

        $this->context->setLog($log);

        $this->commandBus->handle(new StartDeploymentCommand(
            $this->context->getTideUuid(),
            $this->context
        ));
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
