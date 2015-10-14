<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

class TeamContext implements Context
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param Kernel $kernel
     * @param TeamRepository $teamRepository
     */
    public function __construct(Kernel $kernel, TeamRepository $teamRepository)
    {
        $this->kernel = $kernel;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @When I create a team :slug
     */
    public function iCreateATeam($slug)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/v1/teams',
            'POST',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'slug' => $slug
            ])
        ));

        $this->assertResponseCodeIs($this->response, 201);
    }

    /**
     * @Then I should see the team :slug in my teams list
     */
    public function iShouldSeeTheTeamInMyTeamsList($slug)
    {
        $this->response = $this->kernel->handle(Request::create('/api/v1/teams', 'GET'));
        $this->assertResponseCodeIs($this->response, 200);
        $list = json_decode($this->response->getContent(), true);
        $matchingTeam = array_filter($list, function(array $team) use ($slug) {
            return $team['slug'] == $slug;
        });

        if (0 == count($matchingTeam)) {
            throw new \RuntimeException(sprintf(
                'Found 0 team matching in my teams list'
            ));
        }
    }

    /**
     * @Given there is a team :slug
     */
    public function thereIsATeam($slug)
    {
        $this->teamRepository->save(new Team($slug));
    }

    /**
     * @When I add the user :username in the team :teamSlug
     */
    public function iAddTheUserInTheTeam($username, $teamSlug)
    {
        $url = sprintf('/api/v1/teams/%s/users/%s', $teamSlug, $username);
        $this->response = $this->kernel->handle(Request::create($url, 'PUT'));

        $this->assertResponseCodeIs($this->response, 204);
    }

    /**
     * @Then I can see the user :username in the team :teamSlug
     */
    public function iCanSeeTheUserInTheTeam($username, $teamSlug)
    {
        $url = sprintf('/api/v1/teams/%s', $teamSlug);
        $this->response = $this->kernel->handle(Request::create($url, 'GET'));
        $this->assertResponseCodeIs($this->response, 200);

        $userAssociations = json_decode($this->response->getContent(), true)['user_associations'];
        $matchingUsers = array_filter($userAssociations, function(array $association) use ($username) {
            return $association['user']['username'] == $username;
        });

        if (0 == count($matchingUsers)) {
            throw new \RuntimeException(sprintf(
                'Found 0 team matching in my teams list'
            ));
        }
    }

    /**
     * @param Response $response
     * @param int $statusCode
     */
    private function assertResponseCodeIs(Response $response, $statusCode)
    {
        if ($response->getStatusCode() != $statusCode) {
            echo $response->getContent();
            throw new \RuntimeException(sprintf(
                'Expected to get status code %d, got %d',
                $statusCode,
                $response->getStatusCode()
            ));
        }
    }
}
