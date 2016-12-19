<?php

namespace ContinuousPipe\River\Pipeline\Generation;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideGenerated;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Task\NullRunner;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideContext;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Ramsey\Uuid\Uuid;

class CreateFailingTideWhenConfigurationIsWrong implements PipelineTideGenerator
{
    private $decoratedGenerator;
    private $loggerFactory;

    public function __construct(PipelineTideGenerator $decoratedGenerator, LoggerFactory $loggerFactory)
    {
        $this->decoratedGenerator = $decoratedGenerator;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(TideGenerationRequest $request): array
    {
        try {
            return $this->decoratedGenerator->generate($request);
        } catch (TideConfigurationException $e) {
            $logger = $this->loggerFactory->create();
            $logger->child(new Text($e->getMessage()));

            $tideUuid = Uuid::uuid4();
            $events = [
                new TideCreated(TideContext::createTide(
                    $request->getFlow()->getUuid(),
                    $request->getFlow()->getTeam(),
                    $request->getFlow()->getUser(),
                    $tideUuid,
                    $request->getCodeReference(),
                    $logger->getLog(),
                    [],
                    $request->getGenerationTrigger()->getCodeRepositoryEvent()
                )),
                new TideGenerated($tideUuid, $request->getGenerationUuid()),
                new TideFailed($tideUuid, $e->getMessage()),
            ];

            $tide = Tide::fromEvents(
                new NullRunner(),
                new TaskList([]),
                $events
            );

            foreach ($events as $event) {
                $tide->pushNewEvent($event);
            }

            return [
                $tide,
            ];
        }
    }
}
