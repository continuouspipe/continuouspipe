<?php

namespace AppBundle\Controller;

use ContinuousPipe\Alerts\AlertFinder;
use ContinuousPipe\Authenticator\Security\User\SystemUser;
use ContinuousPipe\Authenticator\Team\Request\TeamCreationRequest;
use ContinuousPipe\Authenticator\Team\Request\TeamPartialUpdateRequest;
use ContinuousPipe\Authenticator\Team\TeamCreationException;
use ContinuousPipe\Authenticator\Team\TeamCreator;
use ContinuousPipe\Authenticator\Team\TeamUsageLimitsRepository;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\River\Team\Request\TeamDeletionRequest;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\Team\TeamUsageLimits;
use ContinuousPipe\Security\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="api.controller.team")
 */
class TeamController
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @var TeamCreator
     */
    private $teamCreator;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var AlertFinder
     */
    private $alertFinder;

    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;
    /**
     * @var TeamUsageLimitsRepository
     */
    private $teamUsageLimitsRepository;

    public function __construct(
        TeamRepository $teamRepository,
        TeamMembershipRepository $teamMembershipRepository,
        TeamCreator $teamCreator,
        ValidatorInterface $validator,
        AlertFinder $alertFinder,
        UserBillingProfileRepository $userBillingProfileRepository,
        TeamUsageLimitsRepository $teamUsageLimitsRepository
    ) {
        $this->teamRepository = $teamRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->teamCreator = $teamCreator;
        $this->validator = $validator;
        $this->alertFinder = $alertFinder;
        $this->userBillingProfileRepository = $userBillingProfileRepository;
        $this->teamUsageLimitsRepository = $teamUsageLimitsRepository;
    }

    /**
     * @Route("/teams", methods={"GET"})
     * @ParamConverter("user", converter="authenticator_user", options={"fromSecurityContext"=true})
     * @View
     */
    public function listAction($user)
    {
        if ($user instanceof SystemUser) {
            return $this->teamRepository->findAll();
        } elseif (!$user instanceof User) {
            return new JsonResponse(['message' => 'Forbbiden access'], 403);
        }

        $memberships = $this->teamMembershipRepository->findByUser($user);
        $teams = $memberships->map(function (TeamMembership $membership) {
            return $membership->getTeam();
        });

        return $teams->toArray();
    }

    /**
     * @Route("/teams", methods={"POST"})
     * @ParamConverter("user", converter="authenticator_user", options={"fromSecurityContext"=true})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(TeamCreationRequest $creationRequest, User $user)
    {
        $errors = $this->validator->validate($creationRequest);
        if ($errors->count() > 0) {
            return new JsonResponse([
                'message' => $errors->get(0)->getMessage(),
            ], 400);
        }

        try {
            return $this->teamCreator->create($creationRequest, $user);
        } catch (TeamCreationException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @Route("/teams/{slug}", methods={"PATCH"})
     * @ParamConverter("user", converter="authenticator_user", options={"fromSecurityContext"=true})
     * @ParamConverter("updateRequest", converter="fos_rest.request_body")
     * @ParamConverter("team", converter="authenticator_team")
     * @Security("is_granted('ADMIN', team)")
     * @View
     */
    public function updateAction(Team $team, TeamPartialUpdateRequest $updateRequest, User $user)
    {
        try {
            return $this->teamCreator->update($team, $user, $updateRequest);
        } catch (TeamCreationException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @Route("/teams/{slug}", methods={"DELETE"})
     * @ParamConverter("team", converter="authenticator_team")
     * @ParamConverter("teamDeletionRequest", converter="teamDeletionRequest", options={"slug"="slug"})
     * @Security("is_granted('ADMIN', team)")
     * @View
     */
    public function deleteAction(TeamDeletionRequest $teamDeletionRequest, Team $team)
    {
        $violationList = $this->validator->validate($teamDeletionRequest);
        if ($violationList->count() > 0) {
            return new JsonResponse([
                'error' => $violationList->get(0)->getMessage(),
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $this->teamCreator->delete($team);
    }

    /**
     * @Route("/teams/{slug}", methods={"GET"})
     * @ParamConverter("team", converter="authenticator_team")
     * @Security("is_granted('READ', team)")
     * @View
     */
    public function getAction(Team $team)
    {
        return new Team(
            $team->getSlug(),
            $team->getName(),
            $team->getBucketUuid(),
            $this->teamMembershipRepository->findByTeam($team)->toArray()
        );
    }

    /**
     * @Route("/teams/{slug}/usage-limits", methods={"GET"})
     * @ParamConverter("team", converter="authenticator_team")
     * @Security("is_granted('READ', team)")
     * @View
     */
    public function usageLimitsAction(Team $team)
    {
        return $this->teamUsageLimitsRepository->findByTeam($team);
    }

    /**
     * @Route("/teams/{slug}/users/{username}", methods={"PUT"})
     * @ParamConverter("user", converter="authenticator_user", options={"byUsername"="username"})
     * @ParamConverter("team", converter="authenticator_team")
     * @Security("is_granted('ADMIN', team)")
     * @View
     */
    public function addUserAction(Request $request, Team $team, User $user)
    {
        $memberShipRequest = json_decode($request->getContent(), true);

        $this->teamMembershipRepository->save(new TeamMembership(
            $team,
            $user,
            is_array($memberShipRequest) && array_key_exists('permissions', $memberShipRequest) ? $memberShipRequest['permissions'] : []
        ));
    }

    /**
     * @Route("/teams/{slug}/users/{username}", methods={"DELETE"})
     * @ParamConverter("user", converter="authenticator_user", options={"byUsername"="username"})
     * @ParamConverter("team", converter="authenticator_team")
     * @Security("is_granted('ADMIN', team)")
     * @View
     */
    public function deleteUserAction(Team $team, User $user)
    {
        $this->teamMembershipRepository->remove(new TeamMembership($team, $user));
    }

    /**
     * @Route("/teams/{slug}/alerts", methods={"GET"})
     * @ParamConverter("team", converter="authenticator_team")
     * @Security("is_granted('READ', team)")
     * @View
     */
    public function alertsAction(Team $team)
    {
        return $this->alertFinder->findByTeam($team);
    }

    /**
     * @Route("/teams/{slug}/billing-profile", methods={"GET"})
     * @ParamConverter("team", converter="authenticator_team")
     * @Security("is_granted('READ', team)")
     * @View
     */
    public function billingProfileAction(Team $team)
    {
        return $this->userBillingProfileRepository->findByTeam($team);
    }
}
