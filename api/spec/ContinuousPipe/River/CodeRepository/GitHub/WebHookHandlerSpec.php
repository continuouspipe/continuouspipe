<?php

namespace spec\ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\GitHub\CodeReferenceResolver;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideFactory;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\GitHubRequest;
use PhpSpec\ObjectBehavior;
use SimpleBus\Message\Bus\MessageBus;

class WebHookHandlerSpec extends ObjectBehavior
{
    public function let(TideFactory $tideFactory, MessageBus $eventBus, CodeReferenceResolver $codeReferenceResolver)
    {
        $this->beConstructedWith($tideFactory, $codeReferenceResolver, $eventBus);
    }

    public function it_should_create_a_tide_when_a_push_is_received(TideFactory $tideFactory, GitHubRequest $gitHubRequest, CodeReferenceResolver $codeReferenceResolver, CodeReference $codeReference, PushEvent $pushEvent, Flow $flow, Tide $tide)
    {
        $gitHubRequest->getEvent()->willReturn($pushEvent);
        $codeReferenceResolver->fromPushEvent($pushEvent)->willReturn($codeReference);
        $tideFactory->createFromCodeReference($flow, $codeReference)->shouldBeCalled()->willReturn($tide);
        $tide->popNewEvents()->willReturn([]);

        $this->handle($flow, $gitHubRequest);
    }
}
