<?php

namespace ContinuousPipe\River\Task\Run\Command;

use ContinuousPipe\River\Task\Run\RunContext;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class StartRunCommand
{
    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $uuid;

    /**
     * @JMS\Type("ContinuousPipe\River\Task\Run\RunContext")
     *
     * @var RunContext
     */
    private $context;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $taskId;

    /**
     * @param Uuid       $uuid
     * @param RunContext $context
     * @param int        $taskId
     */
    public function __construct(Uuid $uuid, RunContext $context, $taskId)
    {
        $this->uuid = $uuid;
        $this->context = $context;
        $this->taskId = $taskId;
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

    /**
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }
}
