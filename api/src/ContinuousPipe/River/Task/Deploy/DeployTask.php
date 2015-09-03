<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\TideContext;
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
     * @param MessageBus    $commandBus
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory)
    {
        parent::__construct();

        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param TideContext $context
     */
    public function start(TideContext $context)
    {
        $logger = $this->loggerFactory->from($context->getLog());
        $log = $logger->append(new Text('Deploying environment'));

        $this->commandBus->handle(new StartDeploymentCommand(
            $context->getTideUuid(),
            DeployContext::createDeployContext($context, $log)
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
