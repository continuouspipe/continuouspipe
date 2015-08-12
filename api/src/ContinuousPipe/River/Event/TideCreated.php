<?php

namespace ContinuousPipe\River\Event;

use ContinuousPipe\River\TideContext;

class TideCreated implements TideEvent
{
    /**
     * @var TideContext
     */
    private $tideContext;

    /**
     * @param TideContext $tideContext
     */
    public function __construct(TideContext $tideContext)
    {
        $this->tideContext = $tideContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideContext->getTideUuid();
    }

    /**
     * @return TideContext
     */
    public function getTideContext()
    {
        return $this->tideContext;
    }
}
