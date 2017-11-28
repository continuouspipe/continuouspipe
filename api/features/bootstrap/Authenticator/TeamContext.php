<?php

namespace Authenticator;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\GitHubToken;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\PredefinedRequestMappingMiddleware;
use Ramsey\Uuid\Uuid;
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
     * @var PredefinedRequestMappingMiddleware
     */
    private $kubeStatusRequestMappingMiddleware;

    public function __construct(
        Kernel $kernel,
        TeamRepository $teamRepository,
        TeamMembershipRepository $teamMembershipRepository,
        BucketRepository $bucketRepository,
        PredefinedRequestMappingMiddleware $kubeStatusRequestMappingMiddleware
    ) {
        $this->kernel = $kernel;
        $this->teamRepository = $teamRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->bucketRepository = $bucketRepository;
        $this->kubeStatusRequestMappingMiddleware = $kubeStatusRequestMappingMiddleware;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->securityContext = $scope->getEnvironment()->getContext('Authenticator\SecurityContext');
    }

    /**
     * @Given there is a team :slug
     * @Given there is a team :slug with the credentials bucket :bucketUuid
     */
    public function thereIsATeam($slug, $bucketUuid = null)
    {
        if (!$this->teamRepository->exists($slug)) {
            $this->teamRepository->save(new Team($slug, $slug, null !== $bucketUuid ? Uuid::fromString($bucketUuid) : Uuid::uuid4()));
        }
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
     * @Given the user :username is user of the team :slug
     */
    public function theUserIsUserOfTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $user = $this->securityContext->thereIsAUser($username);
        $this->teamMembershipRepository->save(new TeamMembership($team, $user->getUser(), ['USER']));
    }

    /**
     * @Given the user :username is in the team :slug
     */
    public function theUserIsInTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $user = $this->securityContext->thereIsAUser($username);

        if (!$this->isUserInTeam($team, $username)) {
            $this->teamMembershipRepository->save(new TeamMembership($team, $user->getUser()));
        }
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
                'team' => [
                    'slug' => $slug,
                ],
            ])
        ));
    }

    /**
     * @When I create a team :slug named :name
     */
    public function iCreateATeamNamed($slug, $name)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams',
            'POST',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'team' => [
                    'slug' => $slug,
                    'name' => $name,
                ],
            ])
        ));
    }

    /**
     * @When the team :slug named :name is created
     */
    public function theTeamNamedIsCreated($slug, $name, $creatorUsername = 'geza')
    {
        $this->securityContext->iAmAuthenticatedAsUser($creatorUsername);
        $this->iCreateATeamNamed($slug, $name);
    }

    /**
     * @When I create a team :slug with the billing profile :billingAccountUuid
     * @Given there is a team :slug with the billing profile :billingAccountUuid
     */
    public function iCreateATeamWithTheBillingProfile($slug, $billingAccountUuid)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams',
            'POST',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'team' => [
                    'slug' => $slug,
                ],
                'billing_profile' => [
                    'uuid' => $billingAccountUuid,
                ],
            ])
        ));
    }

    /**
     * @When I update the team :slug with the name :name
     */
    public function iUpdateTheTeamWithTheName($slug, $name)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams/'.$slug,
            'PATCH',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'team' => [
                    'name' => $name,
                ],
            ])
        ));
    }

    /**
     * @When I update the team :slug with the slug :updatedSlug
     */
    public function iUpdateTheTeamWithTheSlug($slug, $updatedSlug)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams/'.$slug,
            'PATCH',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'team' => [
                    'slug' => $updatedSlug,
                ],
            ])
        ));
    }

    /**
     * @When I update the team :slug with the billing profile :billingProfileUuid
     */
    public function iUpdateTheTeamWithTheBillingProfile($slug, $billingProfileUuid)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams/'.$slug,
            'PATCH',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'billing_profile' => [
                    'uuid' => $billingProfileUuid,
                ],
            ])
        ));
    }

    /**
     * @When I request a managed cluster to be created for the team :teamSlug
     */
    public function iRequestAManagedClusterToBeCreatedForTheTeam($teamSlug)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams/'.$teamSlug.'/managed/create-cluster',
            'POST'
        ));
    }

    /**
     * @When I request the list of teams
     */
    public function iRequestTheListOfTeams()
    {
        $this->response = $this->kernel->handle(Request::create('/api/teams', 'GET'));
    }

    /**
     * @When I request the details of team :team
     */
    public function iRequestTheDetailsOfTeam($team)
    {
        $this->response = $this->kernel->handle(Request::create('/api/teams/'.$team, 'GET'));
    }

    /**
     * @When I request the billing profile of the team :team
     */
    public function iRequestTheBillingProfileOfTheTeam($team)
    {
        $this->response = $this->kernel->handle(Request::create('/api/teams/'.$team.'/billing-profile', 'GET'));
    }

    /**
     * @When I request the billing profile :uuid
     */
    public function iRequestTheBillingProfile($uuid)
    {
        $this->response = $this->kernel->handle(Request::create('/api/billing-profile/' . $uuid));
    }

    /**
     * @Then I should see the billing profile invoices link
     */
    public function iShouldSeeTheBillingProfileInvoicesLink()
    {
        $this->assertResponseCodeIs($this->response, 200);

        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if (!isset($json['invoices_url'])) {
            throw new \RuntimeException('Could not find the invoices URL');
        }
    }

    /**
     * @Then I should see that the billing profile is :profileUuid
     */
    public function iShouldSeeThatTheBillingProfileIs($profileUuid)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if ($json['uuid'] != $profileUuid) {
            throw new \RuntimeException(sprintf('Expected billing profile uuid (%s), but got (%s)', $profileUuid, $json['uuid']));
        }
    }

    /**
     * @When I change the billing profile :billingProfileUuid with the plan :planIdentifier
     */
    public function iChangeTheBillingProfileWithThePlan($billingProfileUuid, $planIdentifier)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/billing-profile/'.$billingProfileUuid.'/change-plan',
            'POST',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'plan' => $planIdentifier,
            ])
        ));
    }

    /**
     * @Then I should see that the billing profile have the plan :planIdentifier
     */
    public function iShouldSeeThatTheBillingProfileHaveThePlan($planIdentifier)
    {
        $this->assertResponseCodeIs($this->response, 200);

        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        if ($json['plan']['identifier'] != $planIdentifier) {
            throw new \RuntimeException('Did not find expected plan');
        }
    }

    /**
     * @When I request the details of team :team with the API key :key
     */
    public function iRequestTheDetailsOfTeamWithTheApiKey($team, $key)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams/'.$team, 'GET',
            [],
            [],
            [],
            [
                'HTTP_X_API_KEY' => $key
            ]
        ));
    }

    /**
     * @Then I should see the team details
     */
    public function iShouldSeeTheTeamDetails()
    {
        if ($this->response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf(
                'Expected status 200 but got %d',
                $this->response->getStatusCode()
            ));
        }
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
        $matchingTeam = $this->getTeamInList($slug);

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
        $matchingTeam = $this->getTeamInList($slug);

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
        $url = sprintf('/api/teams/%s/users/%s', $teamSlug, urlencode($username));
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
     * @Then I should see that the team :slug is named :name
     */
    public function iShouldSeeThatTheTeamIsNamed($slug, $name)
    {
        $matchingTeams = $this->getTeamInList($slug);

        if (count($matchingTeams) == 0) {
            throw new \RuntimeException('Team not found in list');
        } else if ($matchingTeams[0]['name'] != $name) {
            throw new \RuntimeException('Team name is not matching');
        }
    }

    /**
     * @Then the team should be successfully created
     * @Then the managed cluster should be created
     */
    public function theTeamShouldBeSuccessfullyCreated()
    {
        $this->assertResponseCodeIs($this->response, 201);
    }

    /**
     * @Then the team should be successfully updated
     * @Then the billing profile should be successfully updated
     */
    public function theTeamShouldBeSuccessfullyUpdated()
    {
        $this->assertResponseCodeIs($this->response, 200);
    }

    /**
     * @Then the team should not be created
     * @Then the team should not be updated
     * @Then the cluster should not be created
     */
    public function theTeamShouldNotBeCreated()
    {
        $this->assertResponseCodeIs($this->response, 400);
    }

    /**
     * @Then I should be told that :message
     */
    public function iShouldBeToldThat($message)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if ($json['message'] != $message) {
            throw new \RuntimeException(sprintf(
                'Got "%s" instead',
                $json['message']
            ));
        }
    }

    /**
     * @Then I should see that the team have an invalid stug
     */
    public function iShouldSeeThatTheTeamHaveAnInvalidStug()
    {
        $this->assertResponseMessageContains('slug');
    }

    /**
     * @Then I should see that the team already exists
     */
    public function iShouldSeeThatTheTeamAlreadyExists()
    {
        $this->assertResponseMessageContains('already exists');
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
                'Found 0 user matching in my teams list'
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

        if ($this->isUserInTeam($team, $username)) {
            throw new \RuntimeException('User found in teams');
        }
    }

    /**
     * @Then the name of the team :slug should be :name
     */
    public function theNameOfTheTeamShouldBe($slug, $name)
    {
        $team = $this->teamRepository->find($slug);

        if ($team->getName() != $name) {
            throw new \RuntimeException(sprintf(
                'Expected name "%s" but found "%s"',
                $name,
                $team->getName()
            ));
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
     * @Then the user :username should not be in the team :slug
     */
    public function theUserShouldNotBeInTheTeam($username, $slug)
    {
        $team = $this->teamRepository->find($slug);
        $memberships = $this->teamMembershipRepository->findByTeam($team);
        $matchingMemberships = $memberships->filter(function(TeamMembership $membership) use ($username) {
            return $membership->getUser()->getUsername() == $username;
        });

        if (0 != $matchingMemberships->count()) {
            throw new \RuntimeException('Found matching membership');
        }
    }

    /**
     * @When I request the alerts of the team :slug
     */
    public function iRequestTheAlertsOfTheTeam($slug)
    {
        $this->response = $this->kernel->handle(Request::create('/api/teams/'.$slug.'/alerts'));

        $this->assertResponseCodeIs($this->response, 200);
    }

    /**
     * @Then I should see the :type alert
     */
    public function iShouldSeeTheAlert($type)
    {
        $alerts = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingAlerts = array_filter($alerts, function($alert) use ($type) {
            return $alert['type'] == $type;
        });

        if (count($matchingAlerts) == 0) {
            throw new \RuntimeException('No matching alert found');
        }

        return reset($matchingAlerts);
    }

    /**
     * @Then I should see the :type alert with the message :message
     */
    public function iShouldSeeTheAlertWithTheMessage($type, $message)
    {
        $alert = $this->iShouldSeeTheAlert($type);

        if ($alert['message'] != $message) {
            throw new \RuntimeException(sprintf(
                'Found message "%s" instead',
                $alert['message']
            ));
        }
    }

    /**
     * @Then I should not see the :type alert
     */
    public function iShouldNotSeeTheAlert($type)
    {
        $alerts = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingAlerts = array_filter($alerts, function($alert) use ($type) {
            return $alert['type'] == $type;
        });

        if (count($matchingAlerts) != 0) {
            throw new \RuntimeException('Matching alert found');
        }
    }

    /**
     * @When I delete the team :slug
     */
    public function iDeleteTheTeam($slug)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams/' . $slug,
            'DELETE'
        ));

        $this->assertResponseCodeIs($this->response, Response::HTTP_NO_CONTENT);
    }

    /**
     * @When I request the limitations for the team :slug
     */
    public function iRequestTheLimitationsForTheTeam($slug)
    {
        $this->response = $this->kernel->handle(Request::create(
            '/api/teams/' . $slug . '/usage-limits',
            'GET'
        ));

        $this->assertResponseCodeIs($this->response, Response::HTTP_OK);
    }

    /**
     * @Then the tides per hour limit for the team :slug should be :tidesPerHour
     */
    public function theLimitationsForTheTeamShouldBe($slug, $tidesPerHour)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if (!array_key_exists('tides_per_hour', $json)) {
            throw new \RuntimeException('There are no tides_per_hour limitation returned for the team ' . $slug);
        } else if ($tidesPerHour != $json['tides_per_hour']) {
            throw new \RuntimeException(sprintf(
                'The limitation for team "%s" should be "%d", received "%d"',
                $slug,
                $tidesPerHour,
                $json['tides_per_hour']
            ));
        }
    }

    /**
     * @Given the kube-status response for the path :path will be a :responseStatusCode response with the following body:
     */
    public function theKubeStatusResponseForThePathWillBeAResponseWithTheFollowingBody($path, $responseStatusCode, PyStringNode $responseBody)
    {
        $this->kubeStatusRequestMappingMiddleware->addMapping([
            'method' => 'GET',
            'path' => '/^'.str_replace(['/', '+'], ['\\/', '\\+'], $path).'$/',
            'response' => new \GuzzleHttp\Psr7\Response($responseStatusCode, [], $responseBody->getRaw()),
        ]);
    }

    /**
     * @When I request the proxied kube-status path :path
     */
    public function iRequestTheProxiedKubeStatusPath($path)
    {
        $this->response = $this->kernel->handle(Request::create('/api/kube-status'.$path));
    }

    /**
     * @Then I should received a response :statusCode with the following content:
     */
    public function iShouldReceivedAResponseWithTheFollowingContent($responseStatusCode, PyStringNode $string)
    {
        $this->assertResponseCodeIs($this->response, $responseStatusCode);

        $foundContents = trim($this->response->getContent());
        $expectedContents = trim($string->getRaw());

        if ($foundContents != $expectedContents) {
            throw new \RuntimeException(sprintf('Got the following content instead: %s', $foundContents));
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

    /**
     * @param $expectedMessage
     */
    private function assertResponseMessageContains($expectedMessage)
    {
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if (!array_key_exists('message', $json)) {
            throw new \RuntimeException('The message do not contain any message');
        } else if (false === strpos($json['message'], $expectedMessage)) {
            throw new \RuntimeException(sprintf(
                'The message "%s" should contain "%s"',
                $json['message'],
                $expectedMessage
            ));
        }
    }

    /**
     * @param string $slug
     *
     * @return array
     */
    private function getTeamInList($slug)
    {
        $this->assertResponseCodeIs($this->response, 200);
        $list = json_decode($this->response->getContent(), true);
        $matchingTeam = array_filter($list, function (array $team) use ($slug) {
            return $team['slug'] == $slug;
        });

        return $matchingTeam;
    }

    /**
     * @param Team $team
     * @param string $username
     *
     * @return bool
     */
    private function isUserInTeam(Team $team, $username)
    {
        $teamMemberships = $this->teamMembershipRepository->findByTeam($team);
        $matchingMemberships = $teamMemberships->filter(function (TeamMembership $membership) use ($username) {
            return $membership->getUser()->getUsername() == $username;
        });

        return 0 !== $matchingMemberships->count();
    }
}
