<?php

namespace ContinuousPipe\Google;

use ContinuousPipe\Google\ContainerEngineCluster\MasterAuthentication;
use ContinuousPipe\Google\ContainerEngineCluster\NodeConfiguration;
use JMS\Serializer\Annotation as JMS;

final class ContainerEngineCluster
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("ContinuousPipe\Google\ContainerEngineCluster\NodeConfiguration")
     * @JMS\SerializedName("nodeConfig")
     *
     * @var NodeConfiguration
     */
    private $nodeConfiguration;

    /**
     * @JMS\Type("ContinuousPipe\Google\ContainerEngineCluster\MasterAuthentication")
     * @JMS\SerializedName("masterAuth")
     *
     * @var MasterAuthentication
     */
    private $masterAuthentication;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("zone")
     *
     * @var string
     */
    private $zone;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("endpoint")
     *
     * @var string
     */
    private $endpoint;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("currentMasterVersion")
     *
     * @var string
     */
    private $currentMasterVersion;

    /**
     * @JMS\Type("integer")
     * @JMS\SerializedName("currentNodeCount")
     *
     * @var string
     */
    private $currentNodeCount;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return NodeConfiguration
     */
    public function getNodeConfiguration(): NodeConfiguration
    {
        return $this->nodeConfiguration;
    }

    /**
     * @return MasterAuthentication
     */
    public function getMasterAuthentication(): MasterAuthentication
    {
        return $this->masterAuthentication;
    }

    /**
     * @return string
     */
    public function getZone(): string
    {
        return $this->zone;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return string
     */
    public function getCurrentMasterVersion(): string
    {
        return $this->currentMasterVersion;
    }

    /**
     * @return string
     */
    public function getCurrentNodeCount(): string
    {
        return $this->currentNodeCount;
    }
}
