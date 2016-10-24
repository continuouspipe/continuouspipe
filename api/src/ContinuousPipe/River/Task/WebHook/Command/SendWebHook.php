<?php

namespace ContinuousPipe\River\Task\WebHook\Command;

use ContinuousPipe\River\WebHook\WebHook;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class SendWebHook
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $taskId;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $logId;

    /**
     * @JMS\Type("ContinuousPipe\River\WebHook\WebHook")
     *
     * @var WebHook
     */
    private $webHook;

    /**
     * @param Uuid    $tideUuid
     * @param string  $taskId
     * @param string  $logId
     * @param WebHook $webHook
     */
    public function __construct(Uuid $tideUuid, string $taskId, string $logId, WebHook $webHook)
    {
        $this->webHook = $webHook;
        $this->tideUuid = $tideUuid;
        $this->taskId = $taskId;
        $this->logId = $logId;
    }

    /**
     * @return WebHook
     */
    public function getWebHook(): WebHook
    {
        return $this->webHook;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid(): Uuid
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getTaskId(): string
    {
        return $this->taskId;
    }

    /**
     * @return string
     */
    public function getLogId(): string
    {
        return $this->logId;
    }
}
