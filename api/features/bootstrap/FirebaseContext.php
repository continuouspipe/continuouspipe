<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
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
}
