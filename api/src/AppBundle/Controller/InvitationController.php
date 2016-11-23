<?php

namespace AppBundle\Controller;

use ContinuousPipe\Authenticator\Invitation\InvitationNotFound;
use ContinuousPipe\Authenticator\Invitation\InvitationToTeamMembershipTransformer;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use ContinuousPipe\Security\User\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/account", service="app.controller.invitation")
 */
class InvitationController
{
    /**
     * @var UserInvitationRepository
     */
    private $userInvitationRepository;

    /**
     * @var InvitationToTeamMembershipTransformer
     */
    private $invitationToTeamMembershipTransformer;

    /**
     * @param UserInvitationRepository $userInvitationRepository
     * @param InvitationToTeamMembershipTransformer $invitationToTeamMembershipTransformer
     */
    public function __construct(UserInvitationRepository $userInvitationRepository, InvitationToTeamMembershipTransformer $invitationToTeamMembershipTransformer)
    {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->invitationToTeamMembershipTransformer = $invitationToTeamMembershipTransformer;
    }

    /**
     * @Route("/invitation/{uuid}/accept", name="accept_invitation")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     */
    public function acceptAction(User $user, $uuid)
    {
        try {
            $invitation = $this->userInvitationRepository->findByUuid(Uuid::fromString($uuid));
        } catch (InvitationNotFound $e) {
            throw new NotFoundHttpException($e->getMessage(), $e->getCode(), $e);
        }

        $this->invitationToTeamMembershipTransformer->transformInvitation(
            $invitation,
            $user
        );

        $this->userInvitationRepository->delete($invitation);

        return new RedirectResponse(
            'https://ui.continuoupipe.io/team/' . $invitation->getTeamSlug() . '/flows'
        );
    }
}
