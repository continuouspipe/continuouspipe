<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\LogStream\LogRelatedObject;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class Build implements LogRelatedObject, \JsonSerializable
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("ContinuousPipe\Builder\Request\BuildRequest")
     *
     * @var BuildRequest
     */
    private $request;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    private function __construct()
    {
    }

    /**
     * @param BuildRequest $request
     *
     * @return Build
     */
    public static function fromRequest(BuildRequest $request)
    {
        $build = new self();
        $build->uuid = Uuid::uuid1();
        $build->request = $request;
        $build->status = self::STATUS_PENDING;

        return $build;
    }

    /**
     * @param string $status
     */
    public function updateStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return BuildRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'build-'.$this->uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => (string) $this->uuid,
            'status' => $this->status,
        ];
    }
}
