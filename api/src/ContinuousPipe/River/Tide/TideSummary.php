<?php

namespace ContinuousPipe\River\Tide;

class TideSummary
{
    /**
     * @var string
     */
    private $status;

    /**
     * @param string $status
     */
    public function __construct($status)
    {
        $this->status = $status;
    }
}
