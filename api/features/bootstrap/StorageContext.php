<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Infrastructure\Firebase\Pipeline\View\Storage\InMemoryPipelineViewStorage;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Repository\FlowRepository;
use Ramsey\Uuid\Uuid;

class StorageContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var InMemoryPipelineViewStorage
     */
    private $pipelineViewStorage;

    /**
     * @var FlowRepository
     */
    private $flowRepository;

    public function __construct(InMemoryPipelineViewStorage $pipelineViewStorage, FlowRepository $flowRepository)
    {
        $this->pipelineViewStorage = $pipelineViewStorage;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @Given the pipeline :pipelineName in flow :flowUuid should be deleted from the permanent storage of views
     */
    public function thePipelineShouldBeDeletedFromThePermanentStorageOfViews($pipelineName, $flowUuid)
    {
        $flow = $this->flowRepository->find(Uuid::fromString($flowUuid));
        $flatFlow = FlatFlow::fromFlow($flow);
        $pipeline = Pipeline::withConfiguration($flatFlow, ['name' => $pipelineName]);

        if (!$this->pipelineViewStorage->isPipelineDeleted($pipeline->getUuid())) {
            throw new \RuntimeException(sprintf(
                'The pipeline named "%s" does not get deleted from view storage.',
                $pipelineName
            ));
        }
    }
}