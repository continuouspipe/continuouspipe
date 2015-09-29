<?php

namespace ContinuousPipe\River\Task\Run\Command;

use ContinuousPipe\River\Task\Run\RunContext;
use ContinuousPipe\River\Task\Run\RunTaskConfiguration;
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
     * @JMS\Type("ContinuousPipe\River\Task\Run\RunTaskConfiguration")
     *
     * @var RunTaskConfiguration
     */
    private $configuration;

    /**
     * @param Uuid                 $uuid
     * @param RunContext           $context
     * @param RunTaskConfiguration $configuration
     */
    public function __construct(Uuid $uuid, RunContext $context, RunTaskConfiguration $configuration)
    {
        $this->uuid = $uuid;
        $this->context = $context;
        $this->configuration = $configuration;
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
        return $this->context->getTaskId();
    }

    /**
     * @return RunTaskConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
