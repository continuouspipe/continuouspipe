<?php

namespace GitHub\Integration;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;

class TraceableInstallationRepository implements InstallationRepository
{

    /**
     * @var InstallationRepository
     */
    private $decoratedRepository;

    /**
     * @var int
     */
    private $apiCallCount = 0;

    public function __construct(InstallationRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * @return Installation[]
     */
    public function findAll()
    {
        ++$this->apiCallCount;

        return $this->decoratedRepository->findAll();
    }

    /**
     * @param GitHubCodeRepository $codeRepository
     *
     * @throws InstallationNotFound
     *
     * @return Installation
     */
    public function findByRepository(GitHubCodeRepository $codeRepository)
    {
        ++$this->apiCallCount;

        return $this->decoratedRepository->findByRepository($codeRepository);
    }

    public function countApiCalls(): int
    {
        return $this->apiCallCount;
    }
}
