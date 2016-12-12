<?php

namespace spec\ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Model\Commit;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use PhpSpec\ObjectBehavior;

class CodeReferenceResolverSpec extends ObjectBehavior
{
    public function it_returns_the_code_reference_of_a_push_event(PushEvent $pushEvent)
    {
        $pushEvent->getReference()->willReturn('refs/heads/master');
        $pushEvent->getAfter()->willReturn('sha');

        $repository = new Repository(
            new User('sroze'),
            'php-example',
            'https://github.com/sroze/php-example',
            false,
            123
        );
        $pushEvent->getRepository()->willReturn($repository);

        $this->fromPushEvent($pushEvent)->shouldBeLike(new CodeReference(
            GitHubCodeRepository::fromRepository($repository),
            'sha',
            'master'
        ));
    }
}
