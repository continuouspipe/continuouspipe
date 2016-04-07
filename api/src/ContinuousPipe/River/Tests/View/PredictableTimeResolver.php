<?php

namespace ContinuousPipe\River\Tests\View;

use ContinuousPipe\River\View\TimeResolver;

class PredictableTimeResolver implements TimeResolver
{
    /**
     * @var \DateTime
     */
    private $current;

    /**
     * @param \DateTime|null $current
     */
    public function __construct(\DateTime $current = null)
    {
        $this->current = $current ?: new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve()
    {
        return $this->current;
    }

    /**
     * @param \DateTime $datetime
     */
    public function setCurrent(\DateTime $datetime)
    {
        $this->current = $datetime;
    }
}
