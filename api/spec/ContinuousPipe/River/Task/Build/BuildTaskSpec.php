<?php

namespace spec\ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Task\Build\BuildContext;
use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use LogStream\Log;
use LogStream\LoggerFactory;
use PhpSpec\ObjectBehavior;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class BuildTaskSpec extends ObjectBehavior
{
    public function let(MessageBus $commandBus, LoggerFactory $loggerFactory, Log $log, BuildContext $buildContext, BuildTaskConfiguration $configuration)
    {
        $this->beConstructedWith($commandBus, $loggerFactory, $buildContext, $configuration);
        $this->apply(new ImageBuildsStarted(
            Uuid::uuid1(),
            [],
            $log->getWrappedObject()
        ));
    }

    public function it_should_fail_if_a_build_fail(ImageBuildsFailed $imageBuildsFailedEvent)
    {
        $imageBuildsFailedEvent->getTideUuid()->willReturn(Uuid::uuid1());
        $this->apply($imageBuildsFailedEvent);

        $this->shouldBeFailed();
    }
}
