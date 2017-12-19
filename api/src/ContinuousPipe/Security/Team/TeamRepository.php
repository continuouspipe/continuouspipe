<?php

namespace ContinuousPipe\Security\Team;

interface TeamRepository
{
    /**
     * Save the given team.
     *
     * @param Team $team
     *
     * @return Team
     */
    public function save(Team $team);

    /**
     * Check if the team exists.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function exists($slug);

    /**
     * Get a team by its slug.
     *
     * @param string $slug
     *
     * @throws TeamNotFound
     *
     * @return Team
     */
    public function find($slug);

    /**
     * Find all the teams.
     *
     * @return Team[]
     */
    public function findAll();

    /**
     * Delete the given team.
     *
     * @param Team $team
     */
    public function delete(Team $team);
}
