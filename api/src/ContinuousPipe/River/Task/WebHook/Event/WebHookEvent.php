<?php

namespace ContinuousPipe\River\Task\WebHook\Event;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Task\TaskEvent;
use ContinuousPipe\River\WebHook\WebHook;
use Ramsey\Uuid\Uuid;

class WebHookEvent implements TideEvent, TaskEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var string
     */
    private $taskId;

    /**
     * @var WebHook
     */
    private $webHook;

    /**
     * @param Uuid    $tideUuid
     * @param string  $taskId
     * @param WebHook $webHook
     */
    public function __construct(Uuid $tideUuid, string $taskId, WebHook $webHook)
    {
        $this->tideUuid = $tideUuid;
        $this->taskId = $taskId;
        $this->webHook = $webHook;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return WebHook
     */
    public function getWebHook(): WebHook
    {
        return $this->webHook;
    }
}
