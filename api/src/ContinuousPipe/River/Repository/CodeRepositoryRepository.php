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
     * @param string $organisation
     *
     * @return CodeRepository[]
     */
    public function findByOrganisation($organisation);

    /**
     * @param string $identifier
     *
     * @throws CodeRepository\CodeRepositoryNotFound
     *
     * @return CodeRepository
     */
    public function findByIdentifier($identifier);
}
