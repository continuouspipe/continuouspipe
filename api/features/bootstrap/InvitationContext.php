<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
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
        $this->userInvitationRepository->save(new UserInvitation($email, $team, [], new \DateTime()));
    }

    /**
     * @Given the user with email :email was invited to be administrator of the team :team
     */
    public function theUserWithEmailWasInvitedToBeAdministratorOfTheTeam($email, $team)
    {
        $this->userInvitationRepository->save(new UserInvitation($email, $team, ['ADMIN'], new \DateTime()));
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
     * @Then I should see the invitation for the user with email :email
     */
    public function iShouldSeeTheInvitationForTheUserWithEmail($email)
    {
        $invitations = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingInvitations = array_filter($invitations, function(array $invitation) use ($email) {
            return $invitation['user_email'] == $email;
        });

        if (count($matchingInvitations) == 0) {
            throw new \RuntimeException(sprintf(
                'No matching user email found in the list of %d invitations',
                count($matchingInvitations)
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
     * @param $response
     * @param $expectedStatusCode
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
}
