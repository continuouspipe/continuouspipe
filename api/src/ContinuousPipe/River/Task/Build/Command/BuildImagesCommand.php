<?php

namespace ContinuousPipe\River\Task\Build\Command;

use ContinuousPipe\River\Task\Build\BuildTaskConfiguration;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class BuildImagesCommand
{
    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $logId;

    /**
     * @JMS\Type("ContinuousPipe\River\Task\Build\BuildTaskConfiguration")
     *
     * @var BuildTaskConfiguration
     */
    private $configuration;

    /**
     * @param Uuid                   $tideUuid
     * @param BuildTaskConfiguration $configuration
     * @param string                 $logId
     */
    public function __construct(Uuid $tideUuid, BuildTaskConfiguration $configuration, $logId)
    {
        $this->tideUuid = $tideUuid;
        $this->logId = $logId;
        $this->configuration = $configuration;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * @return BuildTaskConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
