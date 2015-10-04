<?php

namespace spec\ContinuousPipe\River;

use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRunner;
use PhpSpec\ObjectBehavior;
use Rhumsaa\Uuid\Uuid;

class TideSpec extends ObjectBehavior
{
    public function let(TaskRunner $taskRunner, TaskList $taskList)
    {
        $this->beConstructedWith($taskRunner, $taskList);
    }

    public function it_should_be_running_after_receiving_the_started_event()
    {
        $this->shouldNotBeRunning();
        $this->apply(new TideStarted(Uuid::uuid1()));
        $this->shouldBeRunning();
    }

    public function it_should_not_be_running_if_failed()
    {
        $this->apply(new TideStarted(Uuid::uuid1()));
        $this->apply(new TideFailed(Uuid::uuid1()));
        $this->shouldNotBeRunning();
    }

    public function it_should_not_be_running_if_successful()
    {
        $this->apply(new TideStarted(Uuid::uuid1()));
        $this->apply(new TideSuccessful(Uuid::uuid1()));
        $this->shouldNotBeRunning();
    }
}
