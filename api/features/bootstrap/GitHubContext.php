<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Tests\CodeRepository\Status\FakeCodeStatusUpdater;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class GitHubContext implements Context
{
    /**
     * @var FakeCodeStatusUpdater
     */
    private $fakeCodeStatusUpdater;

    /**
     * @var TideContext
     */
    private $tideContext;

    /**
     * @param FakeCodeStatusUpdater $fakeCodeStatusUpdater
     */
    public function __construct(FakeCodeStatusUpdater $fakeCodeStatusUpdater)
    {
        $this->fakeCodeStatusUpdater = $fakeCodeStatusUpdater;
    }
    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
    }

    /**
     * @Then the GitHub commit status should be :status
     */
    public function theGitHubCommitStatusShouldBe($status)
    {
        $foundStatus = $this->fakeCodeStatusUpdater->getStatusForTideUuid(
            $this->tideContext->getCurrentTideUuid()
        );

        if ($status !== $foundStatus) {
            throw new \RuntimeException(sprintf(
                'Found status "%s" instead of expected "%s"',
                $foundStatus,
                $status
            ));
        }
    }
}
