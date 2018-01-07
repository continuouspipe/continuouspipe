<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use ContinuousPipe\Security\User\User;

class InMemoryCodeRepositoryRepository implements CodeRepositoryRepository
{
    private $codeRepositories = [];

    private $organisationCodeRepositories = [];

    /**
     * {@inheritdoc}
     */
    public function findByOrganisation($organisation)
    {
        return $this->organisationCodeRepositories[$organisation];
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifier($identifier)
    {
        if (!array_key_exists($identifier, $this->codeRepositories)) {
            throw new CodeRepository\CodeRepositoryNotFound(sprintf(
                'Repository "%s" not found',
                $identifier
            ));
        }

        return $this->codeRepositories[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        return $this->codeRepositories;
    }

    /**
     * Add a new code repository.
     *
     * @param CodeRepository $codeRepository
     */
    public function add(CodeRepository $codeRepository)
    {
        $this->codeRepositories[$codeRepository->getIdentifier()] = $codeRepository;
    }

    /**
     * Add a new code repository for an organisation.
     *
     * @param CodeRepository $codeRepository
     * @param string         $organisation
     */
    public function addForOrganisation(CodeRepository $codeRepository, $organisation)
    {
        if (!array_key_exists($organisation, $this->organisationCodeRepositories)) {
            $this->organisationCodeRepositories[$organisation] = [];
        }

        $this->organisationCodeRepositories[$organisation][] = $codeRepository;
    }
}
