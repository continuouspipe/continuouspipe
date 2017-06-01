<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\InMemoryTidesForBranchQuery;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage\InMemoryBranchViewStorage;
use ContinuousPipe\River\Infrastructure\Firebase\Pipeline\View\Storage\InMemoryPipelineViewStorage;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use LogStream\Tree\TreeLog;
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
    /**
     * @var InMemoryTidesForBranchQuery
     */
    private $tidesForBranchQuery;

    public function __construct(InMemoryPipelineViewStorage $pipelineViewStorage, FlowRepository $flowRepository, InMemoryBranchViewStorage $branchViewStorage, InMemoryTidesForBranchQuery $tidesForBranchQuery)
    {
        $this->pipelineViewStorage = $pipelineViewStorage;
        $this->flowRepository = $flowRepository;
        $this->branchViewStorage = $branchViewStorage;
        $this->tidesForBranchQuery = $tidesForBranchQuery;
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

    /**
     * @Given the :branch branch in the repository for the flow :flow has the following tides:
     */
    public function theBranchInTheRepositoryForTheFlowHasTheFollowingTides($branch, $flow, TableNode $table)
    {
        foreach ($table->getHash() as $tide) {
            $this->tidesForBranchQuery->addTide(new Branch($branch), $this->createTideView($flow, $tide['tide']));
        }
    }

    /**
     * @Then the :branch branch for the flow :flow is stored with the following tides:
     */
    public function theBranchForTheFlowHasTheFollowingTidesStored($branch, $flow, TableNode $table)
    {
        $tides = array_map(function($t) use ($flow) {return $this->createTideView($flow, $t['tide']);}, $table->getHash());

        if (!$this->branchViewStorage->wasBranchSaved(Uuid::fromString($flow), new Branch($branch, $tides))) {
            throw new \RuntimeException(sprintf(
                'The branch "%s" did not get saved in view storage.',
                $branch
            ));
        }
    }

    private function createTideView($flow, $tide)
    {
        return Tide::create(
            Uuid::fromString($tide),
            Uuid::fromString($flow),
            new CodeReference(new GitHubCodeRepository('a', 'b', 'c', 'd', true)),
            TreeLog::fromId($tide),
            new Team('a', 'b'),
            new User('a', Uuid::fromString('2a698c5c-837c-4352-9eeb-49addc0ead19')),
            [],
            new \DateTime()
        );
    }
}