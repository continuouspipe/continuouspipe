<?php

namespace GitHub\Integration;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;

interface InstallationRepository
{
    /**
     * @return Installation[]
     */
    public function findAll();

    /**
     * @param GitHubCodeRepository $codeRepository
     *
     * @throws InstallationNotFound
     *
     * @return Installation
     */
    public function findByRepository(GitHubCodeRepository $codeRepository);
}
