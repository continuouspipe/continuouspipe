<?php

namespace spec\ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Model\Commit;
use GitHub\WebHook\Model\Repository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CodeReferenceResolverSpec extends ObjectBehavior
{
    function it_returns_the_code_reference_of_a_push_event(PushEvent $pushEvent, Commit $commit, Repository $repository)
    {
        $pushEvent->getReference()->willReturn('refs/heads/master');
        $pushEvent->getHeadCommit()->willReturn($commit);
        $commit->getId()->willReturn('sha');
        $pushEvent->getRepository()->willReturn($repository);

        $this->fromPushEvent($pushEvent)->shouldBeLike(new CodeReference(
            new GitHubCodeRepository($repository->getWrappedObject()),
            'sha',
            'master'
        ));
    }
}
