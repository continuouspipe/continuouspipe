<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\User\User;

interface CodeRepositoryRepository
{
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

    /**
     * @param User $user
     *
     * @return CodeRepository[]
     */
    public function findByUser(User $user);
}
