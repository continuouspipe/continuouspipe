<?php

namespace ContinuousPipe\Builder\Request;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Logging;
use ContinuousPipe\Builder\Notification;
use Ramsey\Uuid\Uuid;

class BuildRequest
{
    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var Logging
     */
    private $logging;

    /**
     * @var Uuid
     */
    private $credentialsBucket;

    /**
     * @var BuildStepConfiguration[]
     */
    private $steps;

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @return Logging
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @return Uuid
     */
    public function getCredentialsBucket()
    {
        return $this->credentialsBucket;
    }

    /**
     * @return BuildStepConfiguration[]
     */
    public function getSteps()
    {
        return $this->steps;
    }
}
