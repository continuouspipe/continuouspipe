<?php

namespace ContinuousPipe\Google\ContainerEngineCluster;

use JMS\Serializer\Annotation as JMS;

final class NodeConfiguration
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("machineType")
     *
     * @var string
     */
    private $machineType;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("imageType")
     *
     * @var string
     */
    private $imageType;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("serviceAccount")
     *
     * @var string
     */
    private $serviceAccount;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("diskSizeGb")
     *
     * @var string
     */
    private $diskSizeGb;
}
