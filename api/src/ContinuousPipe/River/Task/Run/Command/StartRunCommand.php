<?php

namespace ContinuousPipe\River\Task\Run\Command;

use ContinuousPipe\River\Task\Run\RunTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class StartRunCommand
{
    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @JMS\Type("ContinuousPipe\River\Task\Run\RunTaskConfiguration")
     *
     * @var RunTaskConfiguration
     */
    private $configuration;

    /**
     * @JMS\Type("ContinuousPipe\River\Task\TaskDetails")
     *
     * @var TaskDetails
     */
    private $taskDetails;

    /**
     * @param Uuid                 $tideUuid
     * @param TaskDetails          $taskDetails
     * @param RunTaskConfiguration $configuration
     */
    public function __construct(Uuid $tideUuid, TaskDetails $taskDetails, RunTaskConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->tideUuid = $tideUuid;
        $this->taskDetails = $taskDetails;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return TaskDetails
     */
    public function getTaskDetails()
    {
        return $this->taskDetails;
    }

    /**
     * @return RunTaskConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
