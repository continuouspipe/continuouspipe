<?php

namespace ApiBundle\Controller;

use ApiBundle\Request\InviteUserRequest;
use ContinuousPipe\Authenticator\Invitation\UserInvitation;
use ContinuousPipe\Authenticator\Invitation\UserInvitationRepository;
use ContinuousPipe\Security\Team\Team;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route(service="api.controller.invitation")
 */
class InvitationController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var UserInvitationRepository
     */
    private $userInvitationRepository;

    /**
     * @param ValidatorInterface       $validator
     * @param UserInvitationRepository $userInvitationRepository
     */
    public function __construct(ValidatorInterface $validator, UserInvitationRepository $userInvitationRepository)
    {
        $this->validator = $validator;
        $this->userInvitationRepository = $userInvitationRepository;
    }

    /**
     * @Route("/teams/{slug}/invitations", methods={"POST"})
     * @ParamConverter("team", converter="team")
     * @ParamConverter("inviteUserRequest", converter="fos_rest.request_body")
     * @Security("is_granted('ADMIN', team)")
     * @View(statusCode=201)
     */
    public function createAction(Team $team, InviteUserRequest $inviteUserRequest)
    {
        $violations = $this->validator->validate($inviteUserRequest);
        if ($violations->count() > 0) {
            return new JsonResponse([
                'error' => $violations->get(0)->getMessage(),
            ], 400);
        }

        $invitation = $this->userInvitationRepository->save(
            new UserInvitation($inviteUserRequest->email, $team->getSlug(), $inviteUserRequest->permissions ?: [], new \DateTime())
        );

        return $invitation;
    }
}
