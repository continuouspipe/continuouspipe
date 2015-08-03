<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Flow;
use ContinuousPipe\User\User;
use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\ImageBuildsStarted;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use GitHub\WebHook\Model\Repository as GitHubRepository;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class TideContext implements Context
{
    /**
     * @var Uuid|null
     */
    private $tideUuid;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param MessageBus $commandBus
     * @param EventStore $eventStore
     */
    public function __construct(MessageBus $commandBus, EventStore $eventStore)
    {
        $this->commandBus = $commandBus;
        $this->eventStore = $eventStore;
    }

    /**
     * @When a tide is started
     */
    public function aTideIsStarted()
    {
        $this->tideUuid = Uuid::uuid1();

        $this->commandBus->handle(new StartTideCommand(
            $this->tideUuid,
            Flow::fromUserAndCodeRepository(
                new User('my@ema.l'),
                new GitHubCodeRepository(
                    new GitHubRepository('foo', 'http://github.com/foo/bar')
                )
            ),
            new CodeReference('master')
        ));
    }

    /**
     * @Then it should build the application images
     */
    public function itShouldBuildTheApplicationImages()
    {
        $events = $this->eventStore->findByTideUuid($this->tideUuid);
        $numberOfImageBuildsStartedEvents = count(array_filter($events, function(TideEvent $event) {
            return $event instanceof ImageBuildsStarted;
        }));

        if (1 !== $numberOfImageBuildsStartedEvents) {
            throw new \Exception(sprintf(
                'Found %d image builds started event, expected 1',
                $numberOfImageBuildsStartedEvents
            ));
        }
    }

    /**
     * @Given there is :number application images in the repository
     */
    public function thereIsApplicationImagesInTheRepository($number)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @Then it should build the :number application images
     */
    public function itShouldBuildTheApplicationImages2($number)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @Given an image build was started
     */
    public function anImageBuildWasStarted()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @When the build is failing
     */
    public function theBuildIsFailing()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @Then the tide should be failed
     */
    public function theTideShouldBeFailed()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @Given :number images builds were started
     */
    public function imagesBuildsWereStarted($number)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @When one image build is successful
     */
    public function oneImageBuildIsSuccessful()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @Then the image builds should be waiting
     */
    public function theImageBuildsShouldBeWaiting()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @When one image build is failed
     */
    public function oneImageBuildIsFailed()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @When :number image builds are successful
     */
    public function imageBuildsAreSuccessful($number)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @Then the image builds should be successful
     */
    public function theImageBuildsShouldBeSuccessful()
    {
        throw new \Exception('Not implemented');
    }
}
