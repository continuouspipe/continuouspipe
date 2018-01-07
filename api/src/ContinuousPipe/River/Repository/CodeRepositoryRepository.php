<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\User\User;

interface CodeRepositoryRepository
{
    /**
     * @param string $organisation
     *
     * @deprecated Use the code repository explorer instead
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
     * @deprecated Use the code repository explorer instead
     *
     * @return CodeRepository[]
     */
    public function findByUser(User $user);
}
