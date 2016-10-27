<?php

namespace ContinuousPipe\River\Task\WebHook\Event;

use ContinuousPipe\River\WebHook\WebHook;
use ContinuousPipe\River\WebHook\WebHookException;
use Ramsey\Uuid\Uuid;

class WebHookFailed extends WebHookEvent
{
    /**
     * @var WebHookException
     */
    private $exception;

    /**
     * @param Uuid             $tideUuid
     * @param string           $taskId
     * @param WebHook          $webHook
     * @param WebHookException $exception
     */
    public function __construct(Uuid $tideUuid, string $taskId, WebHook $webHook, WebHookException $exception)
    {
        parent::__construct($tideUuid, $taskId, $webHook);

        $this->exception = $exception;
    }

    /**
     * @return WebHookException
     */
    public function getException(): WebHookException
    {
        return $this->exception;
    }
}
