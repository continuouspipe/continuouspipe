<?php

namespace ContinuousPipe\Builder\Tests\Docker;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use LogStream\Logger;

class FakeDockerBuilder implements Builder
{
    /**
     * @var Build[]
     */
    private $builds = [];

    /**
     * {@inheritdoc}
     */
    public function build(Build $build, Logger $logger)
    {
        $this->builds[] = $build;
    }

    /**
     * @return \ContinuousPipe\Builder\Build[]
     */
    public function getBuilds()
    {
        return $this->builds;
    }
}
