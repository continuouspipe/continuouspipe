<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\GitHubToken;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\User\User;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

class TeamContext implements Context
{
    /**
     * @var \SecurityContext
     */
    private $securityContext;

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
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param Kernel $kernel
     * @param TeamRepository $teamRepository
     * @param TeamMembershipRepository $teamMembershipRepository
     * @param BucketRepository $bucketRepository
     */
    public function __construct(Kernel $kernel, TeamRepository $teamRepository, TeamMembershipRepository $teamMembershipRepository, BucketRepository $bucketRepository)
    {
        $this->kernel = $kernel;
        $this->teamRepository = $teamRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->securityContext = $scope->getEnvironment()->getContext('SecurityContext');
    }

    /**
     * @Given there is a team :slug
     */
    public function thereIsATeam($slug)
    {
        $this->teamRepository->save(new Team($slug, Uuid::uuid1()));
    }

    /**
     * @Given the user :username is administrator of the team :slug
     */
    public function theUserIsAdministratorOfTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $user = $this->securityContext->thereIsAUser($username);
        $this->teamMembershipRepository->save(new TeamMembership($team, $user->getUser(), ['ADMIN']));
    }

    /**
     * @Given the user :username is in the team :slug
     */
    public function theUserIsInTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $user = $this->securityContext->thereIsAUser($username);
        $this->teamMembershipRepository->save(new TeamMembership($team, $user->getUser()));
    }

    /**
     * @Given the bucket of the team :slug is the :uuid
     */
    public function theBucketOfTheTeamIsThe($slug, $uuid)
    {
        $team = $this->teamRepository->find($slug);
        $team->setBucketUuid(Uuid::fromString($uuid));
        $this->teamRepository->save($team);
    }

    /**
     * @When I create a team :slug
     */
    public function iCreateATeam($slug)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams',
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
     * @When I request the list of teams
     */
    public function iRequestTheListOfTeams()
    {
        $this->response = $this->kernel->handle(Request::create('/api/teams', 'GET'));
    }

    /**
     * @When I request the list of teams with the API key :key
     */
    public function iRequestTheListOfTeamsWithTheApiKey($key)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams',
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $key
            ]
        ));
    }

    /**
     * @Then I should see the team :slug in the team list
     */
    public function iShouldSeeTheTeamInTheTeamList($slug)
    {
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
     * @Then I should not see the team :slug in the team list
     */
    public function iShouldNotSeeTheTeamInTheTeamList($slug)
    {
        $this->assertResponseCodeIs($this->response, 200);
        $list = json_decode($this->response->getContent(), true);
        $matchingTeam = array_filter($list, function(array $team) use ($slug) {
            return $team['slug'] == $slug;
        });

        if (0 !== count($matchingTeam)) {
            throw new \RuntimeException(sprintf(
                'Found %d team matching in my teams list, while expecting 0',
                count($matchingTeam)
            ));
        }
    }

    /**
     * @When I add the user :username in the team :teamSlug
     */
    public function iAddTheUserInTheTeam($username, $teamSlug)
    {
        $url = sprintf('/api/teams/%s/users/%s', $teamSlug, $username);
        $this->response = $this->kernel->handle(Request::create($url, 'PUT'));
    }


    /**
     * @When I add the user :username in the team :teamSlug with the :permissions permissions
     */
    public function iAddTheUserInTheTeamWithThePermissions($username, $teamSlug, $permissions)
    {
        $permissions = explode(',', $permissions);

        $url = sprintf('/api/teams/%s/users/%s', $teamSlug, $username);
        $this->response = $this->kernel->handle(Request::create($url, 'PUT', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'permissions' => $permissions
        ])));
    }

    /**
     * @When I remove the user :username in the team :teamSlug
     */
    public function iRemoveTheUserInTheTeam($username, $teamSlug)
    {
        $url = sprintf('/api/teams/%s/users/%s', $teamSlug, $username);
        $this->response = $this->kernel->handle(Request::create($url, 'DELETE'));
    }

    /**
     * @Then I can see the user :username in the team :teamSlug
     */
    public function iCanSeeTheUserInTheTeam($username, $teamSlug)
    {
        $url = sprintf('/api/teams/%s', $teamSlug);
        $this->response = $this->kernel->handle(Request::create($url, 'GET'));
        $this->assertResponseCodeIs($this->response, 200);

        $userAssociations = json_decode($this->response->getContent(), true)['memberships'];
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
     * @Then the user :username should be administrator of the team :slug
     */
    public function theUserShouldBeAdministratorOfTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $teamMemberships = $this->teamMembershipRepository->findByTeam($team);
        $matchingMemberships = $teamMemberships->filter(function(TeamMembership $membership) use ($username) {
            return $membership->getUser()->getUsername() == $username;
        });

        if (0 == $matchingMemberships->count()) {
            throw new \RuntimeException('User not found in team');
        }

        /** @var TeamMembership $matchingMembership */
        $matchingMembership = $matchingMemberships->first();
        if (!in_array('ADMIN', $matchingMembership->getPermissions())) {
            throw new \RuntimeException('User is not administator');
        }
    }

    /**
     * @Then the user :username shouldn't be in the team :slug
     */
    public function theUserShouldnTBeInTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $teamMemberships = $this->teamMembershipRepository->findByTeam($team);
        $matchingMemberships = $teamMemberships->filter(function(TeamMembership $membership) use ($username) {
            return $membership->getUser()->getUsername() == $username;
        });

        if (0 !== $matchingMemberships->count()) {
            throw new \RuntimeException('User found in teams');
        }
    }

    /**
     * @Then the user should be added to the team
     */
    public function theUserShouldBeAddedToTheTeam()
    {
        $this->assertResponseCodeIs($this->response, 204);
    }

    /**
     * @Then the user should be deleted from the team
     */
    public function theUserShouldBeDeletedFromTheTeam()
    {
        $this->assertResponseCodeIs($this->response, 204);
    }

    /**
     * @Then I should be told that I don't have the authorization
     */
    public function iShouldBeToldThatIDonTHaveTheAuthorization()
    {
        $this->assertResponseCodeIs($this->response, 403);
    }

    /**
     * @Then the team :slug should have a credentials bucket
     */
    public function theTeamShouldHaveACredentialsBucket($slug)
    {
        if ($this->teamRepository->find($slug)->getBucketUuid() == null) {
            throw new \RuntimeException('No bucket found');
        }
    }

    /**
     * @Then the bucket of the team :team should contain the GitHub token :token
     */
    public function theBucketOfTheTeamShouldContainTheGithubToken($team, $token)
    {
        $team = $this->teamRepository->find($team);
        $bucket = $this->bucketRepository->find($team->getBucketUuid());
        $matchingTokens = $bucket->getGitHubTokens()->filter(function(GitHubToken $found) use ($token) {
            return $found->getAccessToken() == $token;
        });

        if (0 == count($matchingTokens)) {
            throw new \RuntimeException('No matching token found');
        }
    }

    /**
     * @Then the team :slug should exists
     */
    public function theTeamShouldExists($slug)
    {
        $this->teamRepository->find($slug);
    }

    /**
     * @Then the user :username should be in the team :slug
     */
    public function theUserShouldBeInTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $memberships = $this->teamMembershipRepository->findByTeam($team);
        $matchingMemberships = $memberships->filter(function(TeamMembership $membership) use ($username) {
            return $membership->getUser()->getUsername() == $username;
        });

        if (0 == $matchingMemberships->count()) {
            throw new \RuntimeException('Found no matching memberships');
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
