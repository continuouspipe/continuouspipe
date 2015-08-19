<?php

namespace spec\ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\GitHub\CodeReferenceResolver;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View\TideRepository;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\GitHubRequest;
use PhpSpec\ObjectBehavior;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class WebHookHandlerSpec extends ObjectBehavior
{
    public function let(
        TideFactory $tideFactory,
        CodeReferenceResolver $codeReferenceResolver,
        MessageBus $eventBus,
        TideRepository $tideRepository,
        EventStore $eventStore,
        PullRequestDeploymentNotifier $pullRequestDeploymentNotifier)
    {
        $this->beConstructedWith(
            $tideFactory,
            $codeReferenceResolver,
            $eventBus,
            $tideRepository,
            $eventStore,
            $pullRequestDeploymentNotifier
        );
    }

    public function it_should_create_a_tide_when_a_push_is_received(
        TideFactory $tideFactory,
        GitHubRequest $gitHubRequest,
        CodeReferenceResolver $codeReferenceResolver,
        CodeReference $codeReference,
        PushEvent $pushEvent,
        Flow $flow,
        Tide $tide)
    {
        $uuid = Uuid::fromString('de305d54-75b4-431b-adb2-eb6b9e546014');
        $gitHubRequest->getEvent()->willReturn($pushEvent);
        $codeReferenceResolver->fromPushEvent($pushEvent)->willReturn($codeReference);
        $tideFactory->createFromCodeReference($flow, $codeReference)->shouldBeCalled()->willReturn($tide);
        $tide->getUuid()->willReturn($uuid);
        $tide->popNewEvents()->willReturn([]);

        $this->handle($flow, $gitHubRequest);
    }
}
