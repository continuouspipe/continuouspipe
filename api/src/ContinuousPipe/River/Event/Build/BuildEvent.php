<?php

namespace ContinuousPipe\River\Event\Build;

use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

abstract class BuildEvent implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var BuilderBuild
     */
    private $build;

    public function __construct(Uuid $tideUuid, BuilderBuild $build)
    {
        $this->tideUuid = $tideUuid;
        $this->build = $build;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return BuilderBuild
     */
    public function getBuild()
    {
        return $this->build;
    }
}
