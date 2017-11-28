<?php

namespace ContinuousPipe\Alerts;

class Alert
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @var AlertAction
     */
    private $action;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @param string             $type
     * @param string             $message
     * @param \DateTimeInterface $date
     * @param AlertAction        $action
     */
    public function __construct($type, $message, \DateTimeInterface $date, AlertAction $action)
    {
        $this->type = $type;
        $this->message = $message;
        $this->date = $date;
        $this->action = $action;
    }
}
