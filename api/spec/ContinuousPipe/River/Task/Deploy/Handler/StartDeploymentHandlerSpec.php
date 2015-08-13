<?php

namespace spec\ContinuousPipe\River\Task\Deploy\Handler;

use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\User\User;
use LogStream\LoggerFactory;
use PhpSpec\ObjectBehavior;
use Rhumsaa\Uuid\Uuid;
use ContinuousPipe\Pipe\Client\DeploymentRequest;
use SimpleBus\Message\Bus\MessageBus;

class StartDeploymentHandlerSpec extends ObjectBehavior
{
    public function let(DeploymentRequestFactory $deploymentRequestFactory, Client $pipeClient, MessageBus $eventBus, LoggerFactory $loggerFactory)
    {
        $this->beConstructedWith($deploymentRequestFactory, $pipeClient, $eventBus, $loggerFactory);
    }

    public function it_handles_start_deployment_command(DeploymentRequestFactory $deploymentRequestFactory, MessageBus $eventBus, Client $pipeClient, DeploymentRequest $deploymentRequest, Client\Deployment $deployment, DeployContext $deployContext)
    {
        $tideUuid = Uuid::uuid1();

        $user = new User('e@mail');
        $deployContext->getUser()->willReturn($user);
        $deploymentRequestFactory->create($deployContext)->shouldBeCalled()->willReturn($deploymentRequest);
        $pipeClient->start($deploymentRequest, $user)->shouldBeCalled()->willReturn($deployment);
        $eventBus->handle(new DeploymentStarted($tideUuid, $deployment->getWrappedObject()))->shouldBeCalled();

        $this->handle(new StartDeploymentCommand($tideUuid, $deployContext->getWrappedObject()));
    }
}
