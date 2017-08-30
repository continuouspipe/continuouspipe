<?php

namespace ContinuousPipe\Billing\Plan;

use JMS\Serializer\Annotation as JMS;

class Metrics
{
    /**
     * Number of available tides.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $tides;

    /**
     * Number of GB of memory.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $memory;

    /**
     * Number of available private Docker images.
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("docker_image")
     *
     * @var int
     */
    private $dockerImage;

    /**
     * Number of available GB of persistent storage.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $storage;

    public function __construct(
        int $tides,
        int $memory,
        int $dockerImage,
        int $storage
    ) {
        $this->tides = $tides;
        $this->memory = $memory;
        $this->dockerImage = $dockerImage;
        $this->storage = $storage;
    }

    public function getTides(): int
    {
        return $this->tides ?: 0;
    }

    public function getMemory(): int
    {
        return $this->memory ?: 0;
    }

    public function getDockerImage(): int
    {
        return $this->dockerImage ?: 0;
    }

    public function getStorage(): int
    {
        return $this->storage ?: 0;
    }
}
