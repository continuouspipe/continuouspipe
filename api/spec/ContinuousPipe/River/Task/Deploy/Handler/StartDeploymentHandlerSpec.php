<?php

namespace spec\ContinuousPipe\River\Task\Deploy\Handler;

use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\Pipe\Client;
use ContinuousPipe\User\User;
use PhpSpec\ObjectBehavior;
use Rhumsaa\Uuid\Uuid;
use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;
use SimpleBus\Message\Bus\MessageBus;

class StartDeploymentHandlerSpec extends ObjectBehavior
{
    public function let(DeploymentRequestFactory $deploymentRequestFactory, Client $pipeClient, MessageBus $eventBus)
    {
        $this->beConstructedWith($deploymentRequestFactory, $pipeClient, $eventBus);
    }

    public function it_handles_start_deployment_command(DeploymentRequestFactory $deploymentRequestFactory, MessageBus $eventBus, Client $pipeClient, EnvironmentDeploymentRequest $environmentDeploymentRequest, DeployContext $deployContext)
    {
        $tideUuid = Uuid::uuid1();

        $deploymentRequestFactory->create($deployContext)->shouldBeCalled()->willReturn($environmentDeploymentRequest);
        $pipeClient->start($environmentDeploymentRequest, new User('e@mail'))->shouldBeCalled();
        $eventBus->handle(new DeploymentStarted($tideUuid, $environmentDeploymentRequest))->shouldBeCalled();

        $this->handle(new StartDeploymentCommand($tideUuid, $deployContext->getWrappedObject()));
    }
}
