<?php

namespace ContinuousPipe\Builder\View;

use ContinuousPipe\Builder\Aggregate\BuildRepository;
use ContinuousPipe\Builder\Build;

class FromAggregateBuildViewRepository implements BuildViewRepository
{
    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @param BuildRepository $buildRepository
     */
    public function __construct(BuildRepository $buildRepository)
    {
        $this->buildRepository = $buildRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): Build
    {
        $build = $this->buildRepository->find($identifier);

        return new Build(
            $build->getIdentifier(),
            $build->getRequest(),
            $build->getStatus()
        );
    }
}
