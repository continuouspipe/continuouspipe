<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\River\BuildNotFound;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\Build\Command\BuildImagesCommand;
use ContinuousPipe\River\Task\Build\Event\BuildFailed;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use ContinuousPipe\River\Task\Build\Event\BuildSuccessful;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use ContinuousPipe\River\Task\EventDrivenTask;
use ContinuousPipe\River\TideContext;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class BuildTask extends EventDrivenTask
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus    $commandBus
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory)
    {
        parent::__construct();

        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param TideEvent $event
     */
    public function apply(TideEvent $event)
    {
        parent::apply($event);

        if ($event instanceof BuildSuccessful) {
            $this->applyBuildSuccessful($event);
        } elseif ($event instanceof BuildFailed) {
            $this->applyBuildFailed($event);
        }
    }

    /**
     * @param BuildSuccessful $event
     */
    private function applyBuildSuccessful(BuildSuccessful $event)
    {
        if ($this->allImageBuildsSuccessful()) {
            $eventImageBuildsStarted = $this->getImageBuildsStartedEvent();

            $this->newEvents[] = new ImageBuildsSuccessful($event->getTideUuid(), $eventImageBuildsStarted->getLog());
        }
    }

    /**
     * @param BuildFailed $event
     */
    private function applyBuildFailed(BuildFailed $event)
    {
        $eventImageBuildsStarted = $this->getImageBuildsStartedEvent();

        $this->newEvents[] = new ImageBuildsFailed($event->getTideUuid(), $eventImageBuildsStarted->getLog());
    }

    /**
     * Check if all the started builds are successful.
     *
     * @return bool
     *
     * @throws BuildNotFound
     */
    private function allImageBuildsSuccessful()
    {
        /** @var BuildStarted[] $buildStartedEvents */
        $buildStartedEvents = $this->getEventsOfType(BuildStarted::class);
        if (count($buildStartedEvents) == 0) {
            throw new BuildNotFound('No started build found');
        }

        foreach ($buildStartedEvents as $buildStartedEvent) {
            if (!$this->isBuildSuccessful($buildStartedEvent->getBuild())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return ImageBuildsStarted
     */
    private function getImageBuildsStartedEvent()
    {
        $events = $this->getEventsOfType(ImageBuildsStarted::class);
        if (0 === count($events)) {
            throw new \RuntimeException('No `ImageBuildsStarted` event found');
        }

        return current($events);
    }

    /**
     * {@inheritdoc}
     */
    public function start(TideContext $context)
    {
        $logger = $this->loggerFactory->from($context->getLog());
        $log = $logger->append(new Text('Building application images'));

        $this->commandBus->handle(new BuildImagesCommand(
            $context->getTideUuid(),
            $log
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return 0 < $this->numberOfEventsOfType(ImageBuildsSuccessful::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return 0 < $this->numberOfEventsOfType(ImageBuildsFailed::class);
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return 0 === $this->numberOfEventsOfType(ImageBuildsStarted::class);
    }

    /**
     * Return true if the given build is successful.
     *
     * @param BuilderBuild $build
     *
     * @return bool
     */
    private function isBuildSuccessful(BuilderBuild $build)
    {
        $buildSuccessfulEvents = $this->getEventsOfType(BuildSuccessful::class);
        $matchingEvents = array_filter($buildSuccessfulEvents, function (BuildSuccessful $event) use ($build) {
            return $event->getBuild()->getUuid() == $build->getUuid();
        });

        return count($matchingEvents) > 0;
    }
}
