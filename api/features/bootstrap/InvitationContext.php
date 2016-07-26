<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class InvitationContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var UserInvitationRepository
     */
    private $userInvitationRepository;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param KernelInterface $kernel
     * @param UserInvitationRepository $userInvitationRepository
     */
    public function __construct(KernelInterface $kernel, UserInvitationRepository $userInvitationRepository)
    {
        $this->kernel = $kernel;
        $this->userInvitationRepository = $userInvitationRepository;
    }

    /**
     * @Given the user with email :email was invited to join the team :team
     */
    public function theUserWithEmailWasInvitedToJoinTheTeam($email, $team)
    {
        $this->userInvitationRepository->save(new UserInvitation(Uuid::uuid4(), $email, $team, [], new \DateTime()));
    }

    /**
     * @Given the user with email :email was invited to be administrator of the team :team
     */
    public function theUserWithEmailWasInvitedToBeAdministratorOfTheTeam($email, $team)
    {
        $this->userInvitationRepository->save(new UserInvitation(Uuid::uuid4(), $email, $team, ['ADMIN'], new \DateTime()));
    }

    /**
     * @When I invite the user :email to the team :team
     */
    public function iInviteTheUserToTheTeam($email, $team)
    {
        $url = sprintf('/api/teams/%s/invitations', $team);
        $response = $this->kernel->handle(Request::create(
            $url,
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'email' => $email,
            ])
        ));

        $this->assertResponseStatusCode($response, 201);
    }

    /**
     * @When I request the list of invitations for the team :team
     */
    public function iRequestTheListOfInvitationsForTheTeam($team)
    {
        $url = sprintf('/api/teams/%s/invitations', $team);
        $this->response = $this->kernel->handle(Request::create($url));

        $this->assertResponseStatusCode($this->response, 200);
    }

    /**
     * @When I delete the invitation for the user with email :email for the team :team
     */
    public function iDeleteTheInvitationForTheUserWithEmailForTheTeam($email, $team)
    {
        $invitation = $this->findInvitationByUserAndTeam($email, $team);
        $url = sprintf('/api/teams/%s/invitations/%s', $team, $invitation->getUuid());
        $response = $this->kernel->handle(Request::create($url, 'DELETE'));

        $this->assertResponseStatusCode($response, Response::HTTP_NO_CONTENT);
    }

    /**
     * @When I request the status of members for the team :team
     */
    public function iRequestTheStatusOfMembersForTheTeam($team)
    {
        $url = sprintf('/api/teams/%s/members-status', $team);
        $this->response = $this->kernel->handle(Request::create($url, 'GET'));

        $this->assertResponseStatusCode($this->response, Response::HTTP_OK);
    }

    /**
     * @Then I should see the invitation for the user with email :email in the member status
     */
    public function iShouldSeeTheInvitationForTheUserWithEmailInTheMemberStatus($email)
    {
        $body = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $this->extractInvitationsFromResponseForUserWithEmail($email, $body['invitations']);
    }

    /**
     * @Then I should see the user :username in the member status
     */
    public function iShouldSeeTheUserInTheMemberStatus($username)
    {
        $body = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingMemberships = array_filter($body['memberships'], function(array $membership) use ($username) {
            return $membership['user']['username'] == $username;
        });

        if (count($matchingMemberships) == 0) {
            throw new \RuntimeException('No matching membership found in list');
        }
    }

    /**
     * @Then I should see the invitation for the user with email :email
     */
    public function iShouldSeeTheInvitationForTheUserWithEmail($email)
    {
        $invitations = $this->extractInvitationsFromResponseForUserWithEmail($email);

        if (count($invitations) == 0) {
            throw new \RuntimeException(sprintf(
                'No matching user email found in the list of %d invitations',
                count($invitations)
            ));
        }
    }

    /**
     * @Then I should not see the invitation for the user with email :email
     */
    public function iShouldNotSeeTheInvitationForTheUserWithEmail($email)
    {
        $invitations = $this->extractInvitationsFromResponseForUserWithEmail($email);

        if (count($invitations) != 0) {
            throw new \RuntimeException(sprintf(
                'Found matching user email found in the list of %d invitations',
                count($invitations)
            ));
        }
    }

    /**
     * @Then the invitation for the user :email should be created
     */
    public function theInvitationForTheUserShouldBeCreated($email)
    {
        $invitations = $this->userInvitationRepository->findByUserEmail($email);

        if (count($invitations) == 0) {
            throw new \RuntimeException(sprintf(
                'Found no invitation for user "%s"',
                $email
            ));
        }
    }

    /**
     * @param Response $response
     * @param int $expectedStatusCode
     */
    private function assertResponseStatusCode($response, $expectedStatusCode)
    {
        if ($response->getStatusCode() !== $expectedStatusCode) {
            echo $response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code %d but got %d',
                $expectedStatusCode,
                $response->getStatusCode()
            ));
        }
    }

    /**
     * @param string $email
     *
     * @return array
     */
    private function extractInvitationsFromResponseForUserWithEmail($email, $invitations = null)
    {
        $invitations = $invitations ?: \GuzzleHttp\json_decode($this->response->getContent(), true);

        return array_filter($invitations, function(array $invitation) use ($email) {
            return $invitation['user_email'] == $email;
        });
    }

    /**
     * @param string $email
     * @param string $team
     *
     * @return UserInvitation
     */
    private function findInvitationByUserAndTeam($email, $team)
    {
        $invitationsByUser = $this->userInvitationRepository->findByUserEmail($email);
        $invitationsByTeamAndUser = array_filter($invitationsByUser, function (UserInvitation $invitation) use ($team) {
            return $invitation->getTeamSlug() == $team;
        });

        if (count($invitationsByTeamAndUser) == 0) {
            throw new \RuntimeException('No matching invitation found');
        }

        return current($invitationsByTeamAndUser);
    }
}
