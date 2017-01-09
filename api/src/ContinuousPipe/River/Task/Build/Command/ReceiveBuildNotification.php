<?php

namespace ContinuousPipe\River\Task\Build\Command;

use ContinuousPipe\Builder\Client\BuilderBuild;
use Ramsey\Uuid\UuidInterface;

class ReceiveBuildNotification
{
    /**
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @var BuilderBuild
     */
    private $build;

    public function __construct(UuidInterface $tideUuid, BuilderBuild $build)
    {
        $this->tideUuid = $tideUuid;
        $this->build = $build;
    }

    /**
     * @return UuidInterface
     */
    public function getTideUuid(): UuidInterface
    {
        return $this->tideUuid;
    }

    /**
     * @return BuilderBuild
     */
    public function getBuild(): BuilderBuild
    {
        return $this->build;
    }
}
