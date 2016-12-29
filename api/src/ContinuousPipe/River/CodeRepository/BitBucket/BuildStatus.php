<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use JMS\Serializer\Annotation as JMS;

class BuildStatus
{
    const STATE_SUCCESSFUL = 'SUCCESSFUL';
    const STATE_FAILED = 'FAILED';
    const STATE_IN_PROGRESS = 'INPROGRESS';
    const STATE_STOPPED = 'STOPPED';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $url;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("refname")
     *
     * @var string
     */
    private $referenceName;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $state;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $key;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $updatedOn;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $createdOn;

    public function __construct(string $key, string $uuid = null)
    {
        $this->key = $key;
        $this->uuid = $uuid;
    }

    public function withUrl(string $url) : BuildStatus
    {
        return $this->with('url', $url);
    }

    public function withState(string $state) : BuildStatus
    {
        return $this->with('state', $state);
    }

    public function withDescription(string $description) : BuildStatus
    {
        return $this->with('description', $description);
    }

    private function with(string $property, $value) : BuildStatus
    {
        $status = clone $this;
        $status->$property = $value;

        return $status;
    }
}
