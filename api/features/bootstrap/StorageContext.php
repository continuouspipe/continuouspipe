<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage\InMemoryBranchViewStorage;
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

    /**
     * @var InMemoryBranchViewStorage
     */
    private $branchViewStorage;

    public function __construct(InMemoryPipelineViewStorage $pipelineViewStorage, FlowRepository $flowRepository, InMemoryBranchViewStorage $branchViewStorage)
    {
        $this->pipelineViewStorage = $pipelineViewStorage;
        $this->flowRepository = $flowRepository;
        $this->branchViewStorage = $branchViewStorage;
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


    /**
     * @Then the branch :branch for the flow :flow should be saved to the permanent storage of views
     */
    public function theBranchForTheFlowShouldBeDeletedFromThePermanentStorageOfViews($branch, $flow)
    {
        if (!$this->branchViewStorage->wasBranchSaved(Uuid::fromString($flow), new Branch($branch))) {
            throw new \RuntimeException(sprintf(
                'The branch "%s" did not get saved in view storage.',
                $branch
            ));
        }
    }
}