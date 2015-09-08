<?php

namespace ContinuousPipe\River\Task\Run\Command;

use ContinuousPipe\River\Task\Run\RunContext;
use Rhumsaa\Uuid\Uuid;

class StartRunCommand
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var RunContext
     */
    private $context;

    /**
     * @param Uuid       $uuid
     * @param RunContext $context
     */
    public function __construct(Uuid $uuid, RunContext $context)
    {
        $this->uuid = $uuid;
        $this->context = $context;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return RunContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
