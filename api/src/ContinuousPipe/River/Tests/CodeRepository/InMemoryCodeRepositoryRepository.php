<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Repository\CodeRepositoryRepository;

class InMemoryCodeRepositoryRepository implements CodeRepositoryRepository
{
    private $codeRepositories = [];

    /**
     * {@inheritdoc}
     */
    public function findByCurrentUser()
    {
        throw new \LogicException('Not implemented');
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
}
