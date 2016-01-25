<?php

namespace ContinuousPipe\Builder\Logging\Docker;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use LogStream\Logger;
use LogStream\Node\Text;

class LoggedDockerBuilder implements Builder
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Build $build, Logger $logger)
    {
        try {
            $this->builder->build($build, $logger);
        } catch (BuildException $e) {
            $logger->child(new Text($e->getMessage()));

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function push(Build $build, Logger $logger)
    {
        try {
            $this->builder->push($build, $logger);
        } catch (BuildException $e) {
            $logger->child(new Text($e->getMessage()));

            throw $e;
        }
    }
}
