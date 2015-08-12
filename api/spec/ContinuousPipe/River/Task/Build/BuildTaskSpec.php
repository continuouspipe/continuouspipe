<?php

namespace spec\ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use LogStream\Log;
use LogStream\LoggerFactory;
use PhpSpec\ObjectBehavior;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class BuildTaskSpec extends ObjectBehavior
{
    public function let(MessageBus $commandBus, LoggerFactory $loggerFactory, Log $log)
    {
        $this->beConstructedWith($commandBus, $loggerFactory);
        $this->apply(new ImageBuildsStarted(
            Uuid::uuid1(),
            [],
            $log->getWrappedObject()
        ));
    }

    public function it_should_fail_if_a_build_fail(BuildFailed $buildFailedEvent)
    {
        $buildFailedEvent->getTideUuid()->willReturn(Uuid::uuid1());
        $this->apply($buildFailedEvent);

        $this->shouldBeFailed();
    }
}
