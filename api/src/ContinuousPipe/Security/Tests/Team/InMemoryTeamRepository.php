<?php

namespace ContinuousPipe\Security\Tests\Team;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;

class InMemoryTeamRepository implements TeamRepository
{
    /**
     * @var Team[]
     */
    private $teams = [];

    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @param TeamMembershipRepository $teamMembershipRepository
     */
    public function __construct(TeamMembershipRepository $teamMembershipRepository)
    {
        $this->teamMembershipRepository = $teamMembershipRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Team $team)
    {
        $this->teams[$team->getSlug()] = $team;

        return $team;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($slug)
    {
        return array_key_exists($slug, $this->teams);
    }

    /**
     * {@inheritdoc}
     */
    public function find($slug)
    {
        if (!array_key_exists($slug, $this->teams)) {
            throw TeamNotFound::createFromSlug($slug);
        }

        $team = $this->teams[$slug];
        $team->getMemberships()->clear();

        foreach ($this->teamMembershipRepository->findByTeam($team) as $membership) {
            $team->getMemberships()->add($membership);
        }

        return $team;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->teams;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Team $team)
    {
        unset($this->teams[$team->getSlug()]);

        foreach ($this->teamMembershipRepository->findByTeam($team) as $membership) {
            $this->teamMembershipRepository->remove($membership);
        }
    }
}
