<?php

namespace ContinuousPipe\Google\ContainerEngineCluster;

use JMS\Serializer\Annotation as JMS;

final class MasterAuthentication
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("username")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("password")
     *
     * @var string
     */
    private $password;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clusterCaCertificate")
     *
     * @var string
     */
    private $clusterCaCertificate;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clientCertificate")
     *
     * @var string
     */
    private $clientCertificate;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clientKey")
     *
     * @var string
     */
    private $clientKey;
}
