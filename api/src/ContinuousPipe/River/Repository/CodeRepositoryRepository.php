<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\CodeRepository;

interface CodeRepositoryRepository
{
    /**
     * @return CodeRepository[]
     */
    public function findByCurrentUser();

    /**
     * @param string $identifier
     *
     * @return CodeRepository
     */
    public function findByIdentifier($identifier);
}
