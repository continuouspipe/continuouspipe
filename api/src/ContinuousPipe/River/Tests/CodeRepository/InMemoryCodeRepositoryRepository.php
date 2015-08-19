<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Repository\CodeRepositoryRepository;

class InMemoryCodeRepositoryRepository implements CodeRepositoryRepository
{
    private $codeRepositories = [];

    private $organisationCodeRepositories = [];

    /**
     * {@inheritdoc}
     */
    public function findByCurrentUser()
    {
        return $this->codeRepositories;
    }

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
            throw new CodeRepository\CodeRepositoryNotFound();
        }

        return $this->codeRepositories[$identifier];
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
     * Add a new code repository for an organisation
     *
     * @param CodeRepository $codeRepository
     * @param string $organisation
     */
    public function addForOrganisation(CodeRepository $codeRepository, $organisation)
    {
        if (!array_key_exists($organisation, $this->organisationCodeRepositories)) {
            $this->organisationCodeRepositories[$organisation] = [];
        }

        $this->organisationCodeRepositories[$organisation][] = $codeRepository;
    }
}
