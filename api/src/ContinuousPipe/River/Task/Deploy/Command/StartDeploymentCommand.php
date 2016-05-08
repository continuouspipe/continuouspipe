<?php

namespace ContinuousPipe\River\Task\Deploy\Command;

use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
use ContinuousPipe\River\Task\TaskDetails;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class StartDeploymentCommand
{
    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @JMS\Type("ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration")
     *
     * @var DeployTaskConfiguration
     */
    private $configuration;

    /**
     * @JMS\Type("ContinuousPipe\River\Task\TaskDetails")
     *
     * @var TaskDetails
     */
    private $taskDetails;

    /**
     * @param Uuid                    $tideUuid
     * @param TaskDetails             $taskDetails
     * @param DeployTaskConfiguration $configuration
     */
    public function __construct(Uuid $tideUuid, TaskDetails $taskDetails, DeployTaskConfiguration $configuration)
    {
        $this->tideUuid = $tideUuid;
        $this->configuration = $configuration;
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
     * @return DeployTaskConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
