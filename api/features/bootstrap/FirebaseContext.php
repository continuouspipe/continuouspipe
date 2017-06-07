<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use GuzzleHttp\Psr7\Request;

class FirebaseContext implements Context
{
    /**
     * @var History
     */
    private $httpHistory;

    /**
     * @var \TideContext
     */
    private $tideContext;

    public function __construct(History $httpHistory)
    {
        $this->httpHistory = $httpHistory;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
    }

    /**
     * @Then a tide should be wrote in Firebase under the pipeline :pipelineName
     */
    public function aTideShouldBeWroteInFirebaseUnderThePipeline($pipelineName)
    {
        $tides = $this->tideContext->getCreatedTides();
        $pipelines = [];
        foreach ($tides as $tide) {
            if (null !== ($pipeline = $tide->getPipeline())) {
                $pipelines[] = $pipeline;
            }
        }

        $pipeline = null;
        foreach ($pipelines as $flowPipeline) {
            if ($flowPipeline->getName() == $pipelineName) {
                $pipeline = $flowPipeline;
            }
        }

        if (null === $pipeline) {
            throw new \RuntimeException('Pipeline not found');
        }

        foreach ($this->httpHistory as $request) {
            /** @var Request $request */
            $uri = (string) $request->getUri();

            $requestBase = sprintf(
                'https://continuous-pipe.firebaseio.com/flows/%s/tides/by-pipelines/%s/',
                (string) $tides[0]->getFlowUuid(),
                (string) $pipeline->getUuid()
            );

            if (0 === strpos($uri, $requestBase)) {
                return;
            }
        }

        throw new \RuntimeException('Request not found');
    }

    /**
     * @Then the branch :branch for the flow :flow should be saved to the permanent storage of views
     */
    public function theBranchForTheFlowShouldBeSavedToTheFirebaseStorageOfViews($branch, $flow)
    {
        foreach ($this->httpHistory as $request) {
            /** @var Request $request */
            $uri = (string) $request->getUri();

            $requestBase = sprintf(
                'https://continuous-pipe.firebaseio.com/flows/%s/branches/%s',
                $flow,
                $branch
            );

            if (0 === strpos($uri, $requestBase)) {
                return;
            }
        }

        throw new \RuntimeException('Request not found');
    }

    /**
     * @Then the :branch branch for the flow :flow is stored with the following tides:
     */
    public function theBranchForTheFlowHasTheFollowingTidesStored($branch, $flow, TableNode $table)
    {
        $tideUuids = array_map(
            function ($t) {
                return $t['tide'];
            },
            $table->getHash()
        );

        foreach ($this->httpHistory as $request) {
            /** @var Request $request */
            $uri = (string) $request->getUri();

            $requestBase = sprintf(
                'https://continuous-pipe.firebaseio.com/flows/%s/branches/%s',
                $flow,
                $branch
            );

            if (0 === strpos($uri, $requestBase)) {
                $foundTideUuids = array_map(
                    function (array $tide) {
                        return $tide['uuid'];
                    },
                    json_decode($request->getBody()->getContents(), true)['latest-tides']
                );
                foreach ($tideUuids as $tideUuid) {
                    if (!in_array($tideUuid, $foundTideUuids)) {
                        $this->findUpdateRequest($branch, $flow, $tideUuid);
                    }
                }
                return;
            }
        }

        throw new \RuntimeException('Request not found');
    }

    /**
     * @Then the branch :branch for the flow :flow should be saved to the permanent storage of views as a pinned branch
     */
    public function theBranchForTheFlowShouldBeSavedToThePermanentStorageOfViewsAsAPinnedBranch($branch, $flow)
    {
        foreach ($this->httpHistory as $request) {
            /** @var Request $request */
            $uri = (string) $request->getUri();

            $requestBase = sprintf(
                'https://continuous-pipe.firebaseio.com/flows/%s/branches/%s',
                $flow,
                $branch
            );

            if (0 === strpos($uri, $requestBase)) {
                if (json_decode($request->getBody()->getContents(), true) == ['pinned' => true]) {
                    return;
                }
            }
        }

        throw new \RuntimeException('Request not found');
    }

    /**
     * @Then the branch :branch for the flow :flow should be saved to the permanent storage of views as an unpinned branch
     */
    public function theBranchForTheFlowShouldBeSavedToThePermanentStorageOfViews($branch, $flow)
    {
        foreach ($this->httpHistory as $request) {
            /** @var Request $request */
            $uri = (string) $request->getUri();

            $requestBase = sprintf(
                'https://continuous-pipe.firebaseio.com/flows/%s/branches/%s',
                $flow,
                $branch
            );

            if (0 === strpos($uri, $requestBase)) {
                if (json_decode($request->getBody()->getContents(), true) == ['pinned' => false]) {
                    return;
                }
            }
        }

        throw new \RuntimeException('Request not found');
    }

    private function findUpdateRequest($branch, $flow, $tideUuid)
    {
        $updateRequestBase = sprintf(
            'https://continuous-pipe.firebaseio.com/flows/%s/branches/%s/latest-tides/%',
            $flow,
            $branch,
            $tideUuid
        );

        foreach ($this->httpHistory as $request) {
            if (0 === strpos((string) $request->getUri(), $updateRequestBase)) {
                return;
            }
        }

        throw new \RuntimeException('Update request not found');
    }

}
