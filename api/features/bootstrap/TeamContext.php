<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use Helpers\KernelClientHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class TeamContext implements Context
{
    use KernelClientHelper;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(KernelInterface $kernel, TeamRepository $teamRepository)
    {
        $this->kernel = $kernel;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @When I delete the team named :slug
     */
    public function iDeleteTheTeamNamed($slug)
    {
        $this->request(Request::create('/teams/'.$slug, 'DELETE'));
    }

    /**
     * @When I request the usage of the teams :teams from the :left to :right with a :interval interval
     */
    public function iRequestTheUsageOfTheTeamsFromTheToWithAInterval($teams, $left, $right, $interval)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/usage/aggregated',
            'GET',
            [
                'teams' => $teams,
                'left' => $left,
                'right' => $right,
                'interval' => $interval,
            ]
        ));
    }

    /**
     * @Then the team is successfully deleted
     */
    public function theTeamIsSuccessfullyDeleted()
    {
        $this->assertResponseCode(Response::HTTP_NO_CONTENT);
    }

    /**
     * @Given I should not see the team :slug
     */
    public function iShouldNotSeeTheTeam($slug)
    {
        try {
            $this->teamRepository->find($slug);
            throw new \RuntimeException('Team exists, but not expected.');
        } catch (TeamNotFound $e) {
        }
    }

    /**
     * @Then the team deletion should fail
     */
    public function theTeamDeletionShouldFail()
    {
        $this->assertResponseCode(Response::HTTP_FORBIDDEN);
    }

    /**
     * @Given I should be notified that
     */
    public function iShouldBeNotifiedThat(PyStringNode $message)
    {
        $response = $this->jsonResponse();
        if ($message != $response['error']) {
            throw new UnexpectedValueException(sprintf("Error message does not match:\n %s", $response['error']));
        }
    }

    /**
     * @Then I should see that on the :dateTime the flow :flow from the team :team used :count tide
     */
    public function iShouldSeeThatOnTheTheFlowFromTheTeamUsedTide($dateTime, $flow, $team, $count)
    {
        $this->assertResponseCode(200);

        $usageCollection = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $usage = $this->getRowForDate($usageCollection, $dateTime);

        foreach ($usage['entries'] as $item) {
            if ($item['flow']['uuid'] == $flow && $item['team']['slug'] == $team) {
                if ($item['usage']['tides'] != $count) {
                    throw new \RuntimeException(sprintf(
                        'Found %d tides instead of %d',
                        $item['tides'],
                        $count
                    ));
                }

                return;
            }
        }

        throw new \RuntimeException('Did not find such item');
    }

    private function getRowForDate(array $usageCollection, string $dateTime)
    {
        $expectedDateTime = new \DateTime($dateTime);
        $foundDates = [];

        // Find the usage
        foreach ($usageCollection as $usageRow) {
            $usageDateTime = new \DateTime($usageRow['datetime']['left']);

            if ($usageDateTime == $expectedDateTime) {
                return $usageRow;
            }

            $foundDates[] = $usageRow['datetime']['left'];
        }

        throw new \RuntimeException('No usage found for this date. Found following dates: '.implode(', ', $foundDates));
    }
}
