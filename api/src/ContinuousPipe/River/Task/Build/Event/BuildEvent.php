<?php

namespace ContinuousPipe\River\Task\Build\Event;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\River\Event\TideEvent;
use Ramsey\Uuid\Uuid;

abstract class BuildEvent implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var Build
     */
    private $build;

    public function __construct(Uuid $tideUuid, Build $build)
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
     * @return Build
     */
    public function getBuild()
    {
        return $this->build;
    }
}
