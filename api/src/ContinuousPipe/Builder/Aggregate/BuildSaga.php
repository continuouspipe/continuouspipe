<?php

namespace ContinuousPipe\Builder\Aggregate;

use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFailed;
use ContinuousPipe\Builder\Aggregate\BuildStep\Event\StepFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildEvent;
use ContinuousPipe\Builder\Aggregate\Event\BuildFailed;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Builder\Artifact\ArtifactRemover;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuilderClient;
use ContinuousPipe\Events\Transaction\TransactionManager;
use Psr\Log\LoggerInterface;

class BuildSaga
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;
    /**
     * @var ArtifactRemover
     */
    private $artifactRemover;
    /**
     * @var GoogleContainerBuilderClient
     */
    private $googleContainerBuilderClient;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TransactionManager $transactionManager,
        ArtifactRemover $artifactRemover,
        GoogleContainerBuilderClient $googleContainerBuilderClient,
        LoggerInterface $logger
    ) {
        $this->transactionManager = $transactionManager;
        $this->artifactRemover = $artifactRemover;
        $this->googleContainerBuilderClient = $googleContainerBuilderClient;
        $this->logger = $logger;
    }

    public function notify($event)
    {
        if (!method_exists($event, 'getBuildIdentifier')) {
            throw new \InvalidArgumentException(sprintf(
                'The object of class "%s" do not have a `getBuildIdentifier` method',
                get_class($event)
            ));
        }

        $this->transactionManager->apply($event->getBuildIdentifier(), function (Build $build) use ($event) {
            if ($build->isEngine(Engine::GOOGLE_CONTAINER_BUILDER)) {
                if ($event instanceof BuildStarted) {
                    $build->startWithGoogleContainerBuilder($this->googleContainerBuilderClient);
                }
            } else {
                if ($event instanceof StepFailed) {
                    $build->fail();
                } elseif ($event instanceof BuildStarted) {
                    $build->nextStep();
                } elseif ($event instanceof StepFinished) {
                    $build->stepFinished($event);
                } elseif ($event instanceof BuildFinished || $event instanceof BuildFailed) {
                    $build->cleanUp($this->artifactRemover, $this->logger);
                }
            }
        });
    }
}
