<?php

namespace ContinuousPipe\River\Task\WebHook\Event;

use ContinuousPipe\River\WebHook\WebHook;
use Ramsey\Uuid\Uuid;

class WebHookFailed extends WebHookEvent
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @param Uuid    $tideUuid
     * @param string  $taskId
     * @param WebHook $webHook
     * @param string  $reason
     */
    public function __construct(Uuid $tideUuid, string $taskId, WebHook $webHook, string $reason)
    {
        parent::__construct($tideUuid, $taskId, $webHook);

        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
