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
}
