<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Team\Request\TeamDeletionRequest;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\Team\TeamNotFound;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="app.controller.team")
 */
class TeamController
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(AuthenticatorClient $authenticatorClient, ValidatorInterface $validator)
    {
        $this->authenticatorClient = $authenticatorClient;
        $this->validator = $validator;
    }

    /**
     * @Route("/teams/{slug}", name="app_team_delete", methods={"DELETE"})
     * @ParamConverter("teamDeletionRequest", converter="teamDeletionRequest", options={"slug"="slug"})
     * @Security("is_granted('READ', team)")
     * @View
     */
    public function deleteAction(TeamDeletionRequest $teamDeletionRequest)
    {
        try {
            $violationList = $this->validator->validate($teamDeletionRequest);
            if ($violationList->count() > 0) {
                return new JsonResponse([
                    'error' => $violationList->get(0)->getMessage(),
                ], JsonResponse::HTTP_FORBIDDEN);
            }
            $this->authenticatorClient->deleteTeamBySlug($teamDeletionRequest->getTeam()->getSlug());
        } catch (TeamNotFound $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
