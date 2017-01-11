<?php

namespace ContinuousPipe\River\Pipeline\Generation;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideGenerated;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Task\NullRunner;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\TideContext;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class CreateFailingTideWhenConfigurationIsWrong implements PipelineTideGenerator
{
    private $decoratedGenerator;
    private $loggerFactory;
    private $logger;

    public function __construct(PipelineTideGenerator $decoratedGenerator, LoggerFactory $loggerFactory, LoggerInterface $logger)
    {
        $this->decoratedGenerator = $decoratedGenerator;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(TideGenerationRequest $request): array
    {
        try {
            return $this->decoratedGenerator->generate($request);
        } catch (TideConfigurationException $e) {
            $this->logger->debug('Unable to create tide', [
                'exception' => $e,
                'flow_uuid' => (string) $request->getFlow()->getUuid(),
            ]);

            $logger = $this->loggerFactory->create();
            $logger->child(new Text($e->getMessage()));

            $tideUuid = Uuid::uuid4();
            $tide = Tide::createFromEvents(
                new NullRunner(),
                new TaskList([]),
                new EventCollection(),
                [
                    new TideCreated(TideContext::createTide(
                        $request->getFlow()->getUuid(),
                        $request->getFlow()->getTeam(),
                        $request->getFlow()->getUser(),
                        $tideUuid,
                        $request->getCodeReference(),
                        $logger->getLog(),
                        [
                            'notifications' => [
                                [
                                    'commit' => true,
                                ]
                            ]
                        ],
                        $request->getGenerationTrigger()->getCodeRepositoryEvent()
                    )),
                    new TideGenerated($tideUuid, $request->getFlow()->getUuid(), $request->getGenerationUuid(), FlatPipeline::fromPipeline(Pipeline::withConfiguration(
                        $request->getFlow(),
                        [
                            'name' => 'Default pipeline',
                        ]
                    ))),
                    new TideFailed($tideUuid, $e->getMessage()),
                ]
            );

            return [
                $tide,
            ];
        }
    }
}
