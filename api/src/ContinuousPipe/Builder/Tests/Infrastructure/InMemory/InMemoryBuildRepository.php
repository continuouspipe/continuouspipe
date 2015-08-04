<?php

namespace ContinuousPipe\Builder\Tests\Infrastructure\InMemory;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildNotFound;
use ContinuousPipe\Builder\BuildRepository;
use Rhumsaa\Uuid\Uuid;

class InMemoryBuildRepository implements BuildRepository
{
    /**
     * @var array
     */
    private $builds = [];

    /**
     * {@inheritdoc}
     */
    public function save(Build $build)
    {
        return $this->builds[(string) $build->getIdentifier()] = $build;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        $identifier = (string) $uuid;
        if (!array_key_exists($identifier, $this->builds)) {
            throw new BuildNotFound();
        }

        return $this->builds[$identifier];
    }
}
