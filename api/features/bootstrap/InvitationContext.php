<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use Symfony\Component\HttpFoundation\Request;
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
        $url = sprintf('/api/teams/%s/invite', $team);
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

        if ($response->getStatusCode() !== 201) {
            echo $response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code 201 but got %d',
                $response->getStatusCode()
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
}
