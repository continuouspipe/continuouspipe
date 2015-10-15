<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\Security\User\User;

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
     * @param TeamRepository           $teamRepository
     * @param TeamMembershipRepository $teamMembershipRepository
     */
    public function __construct(TeamRepository $teamRepository, TeamMembershipRepository $teamMembershipRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->teamMembershipRepository = $teamMembershipRepository;
    }

    /**
     * @Route("/teams", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function listAction(User $user)
    {
        return $this->teamRepository->findByUser($user);
    }

    /**
     * @Route("/teams", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("team", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(Team $team, User $user)
    {
        $this->teamRepository->save($team);
        $this->teamMembershipRepository->save(new TeamMembership($team, $user, ['ADMIN']));

        return $team;
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
    public function addUserAction(Team $team, User $user)
    {
        $this->teamMembershipRepository->save(new TeamMembership($team, $user));
    }
}
