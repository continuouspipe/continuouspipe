<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\Team\UserAssociation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
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
     * @param TeamRepository $teamRepository
     */
    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
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
    public function createAction(Team $team)
    {
        $this->teamRepository->save($team);

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
     * @View
     */
    public function addUserAction(Team $team, User $user)
    {
        $team->getUserAssociations()->add(new UserAssociation($team, $user));

        $this->teamRepository->save($team);
    }
}
