<?php

namespace ContinuousPipe\River\Serialization;

use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use GitHub\WebHook\Event\PushEvent;
use GitHub\WebHook\Model\Commit;
use JMS\Serializer\SerializationContext;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GitHubEventsSerializationTest extends KernelTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();
    }

    public function test_it_do_not_include_push_event_commits_in_the_serialized_event()
    {
        $serializer = $this->container->get('river.jms_serializer.object_serializer');

        $event = new HandleGitHubEvent(
            Uuid::uuid1(),
            new PushEvent(
                'ref/heads/blah',
                '000000',
                '111111',
                false,
                false,
                false,
                [
                    new Commit(123),
                ]
            )
        );

        $serialized = $serializer->serialize($event);

        /** @var HandleGitHubEvent $deserialized */
        $deserialized = $serializer->deserialize($serialized, HandleGitHubEvent::class);
        $deserializedEvent = $deserialized->getEvent();

        $this->assertCount(0, $deserializedEvent->getCommits(), 'The number of commits found is more than 0');
    }
}
