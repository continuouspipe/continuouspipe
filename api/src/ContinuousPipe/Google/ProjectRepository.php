<?php

namespace ContinuousPipe\Google;

use ContinuousPipe\Security\Account\GoogleAccount;

interface ProjectRepository
{
    /**
     * List all the projects.
     *
     * @param GoogleAccount $account
     *
     * @throws GoogleException
     *
     * @return Project[]
     */
    public function findAll(GoogleAccount $account);
}
