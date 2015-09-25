<?php

namespace ContinuousPipe\River\Task\Deploy\Command;

use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DeployTaskConfiguration;
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
     * @JMS\Type("ContinuousPipe\River\Task\Deploy\DeployContext")
     *
     * @var DeployContext
     */
    private $deployContext;

    /**
     * @var DeployTaskConfiguration
     */
    private $configuration;

    /**
     * @param Uuid                    $tideUuid
     * @param DeployContext           $deployContext
     * @param DeployTaskConfiguration $configuration
     */
    public function __construct(Uuid $tideUuid, DeployContext $deployContext, DeployTaskConfiguration $configuration)
    {
        $this->tideUuid = $tideUuid;
        $this->deployContext = $deployContext;
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
     * @return DeployContext
     */
    public function getDeployContext()
    {
        return $this->deployContext;
    }

    /**
     * @return DeployTaskConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
