<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Authenticator\Team\TeamCreator;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;
use Symfony\Component\HttpFoundation\Request;

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
     * @param TeamRepository           $teamRepository
     * @param TeamMembershipRepository $teamMembershipRepository
     * @param TeamCreator              $teamCreator
     */
    public function __construct(TeamRepository $teamRepository, TeamMembershipRepository $teamMembershipRepository, TeamCreator $teamCreator)
    {
        $this->teamRepository = $teamRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
        $this->teamCreator = $teamCreator;
    }

    /**
     * @Route("/teams", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function listAction(User $user)
    {
        $memberships = $this->teamMembershipRepository->findByUser($user);
        $teams = $memberships->map(function (TeamMembership $membership) {
            return $membership->getTeam();
        });

        return $teams;
    }

    /**
     * @Route("/teams", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("team", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(Team $team, User $user)
    {
        return $this->teamCreator->create($team, $user);
    }

    /**
     * @Route("/teams/{slug}", methods={"GET"})
     * @ParamConverter("team", converter="team")
     * @View
     */
    public function getAction(Team $team)
    {
        return $team;
    }

    /**
     * @Route("/teams/{slug}/users/{username}", methods={"PUT"})
     * @ParamConverter("user", converter="user", options={"byUsername"="username"})
     * @ParamConverter("team", converter="team")
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
     * @ParamConverter("user", converter="user", options={"byUsername"="username"})
     * @ParamConverter("team", converter="team")
     * @Security("is_granted('ADMIN', team)")
     * @View
     */
    public function deleteUserAction(Team $team, User $user)
    {
        $this->teamMembershipRepository->remove(new TeamMembership($team, $user));
    }
}
