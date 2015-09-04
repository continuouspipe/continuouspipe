<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\CodeReference;
use GitHub\WebHook\Event\PullRequestEvent;

class PullRequestClosed
{
    /**
     * @var PullRequestEvent
     */
    private $event;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @param PullRequestEvent $event
     * @param CodeReference    $codeReference
     */
    public function __construct(PullRequestEvent $event, CodeReference $codeReference)
    {
        $this->event = $event;
        $this->codeReference = $codeReference;
    }

    /**
     * @return PullRequestEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }
}
