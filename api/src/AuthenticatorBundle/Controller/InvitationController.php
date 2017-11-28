<?php

namespace AuthenticatorBundle\Controller;

use ContinuousPipe\Authenticator\EarlyAccess\BypassWhiteListToggleFactory;
use ContinuousPipe\Authenticator\Invitation\InvitationNotFound;
use ContinuousPipe\Authenticator\Invitation\InvitationToggleFactory;
use ContinuousPipe\Authenticator\Invitation\InvitationToTeamMembershipTransformer;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use ContinuousPipe\Security\User\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

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
     * @var RouterInterface
     */
    private $router;

    /**
     * @param UserInvitationRepository $userInvitationRepository
     * @param InvitationToTeamMembershipTransformer $invitationToTeamMembershipTransformer
     * @param RouterInterface $router
     */
    public function __construct(
        UserInvitationRepository $userInvitationRepository,
        InvitationToTeamMembershipTransformer $invitationToTeamMembershipTransformer,
        RouterInterface $router
    ) {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->invitationToTeamMembershipTransformer = $invitationToTeamMembershipTransformer;
        $this->router = $router;
    }

    /**
     * @Route("/invitation/{uuid}/accept", name="accept_invitation")
     */
    public function acceptAction($uuid)
    {
        $this->loadInvitation($uuid);

        return new RedirectResponse($this->router->generate('transform_invitation', ['uuid' => $uuid]));
    }

    /**
     * @Route("/invitation/{uuid}/transform", name="transform_invitation")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     */
    public function transformAction(User $user, $uuid)
    {
        $invitation = $this->loadInvitation($uuid);
        $this->invitationToTeamMembershipTransformer->transformInvitation(
            $invitation,
            $user
        );

        $this->userInvitationRepository->delete($invitation);

        return new RedirectResponse(
            'https://ui.continuouspipe.io/team/'.$invitation->getTeamSlug().'/flows'
        );
    }

    private function loadInvitation($uuid): UserInvitation
    {
        try {
            return $this->userInvitationRepository->findByUuid(Uuid::fromString($uuid));
        } catch (InvitationNotFound $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }
}
