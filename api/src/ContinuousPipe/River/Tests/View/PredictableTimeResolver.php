<?php

namespace ContinuousPipe\River\Tests\View;

use ContinuousPipe\River\View\TimeResolver;

class PredictableTimeResolver implements TimeResolver
{
    /**
     * @var TimeResolver
     */
    private $decoratedResolver;

    /**
     * @var \DateTime
     */
    private $current;

    /**
     * @param TimeResolver $decoratedResolver
     */
    public function __construct(TimeResolver $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve()
    {
        return $this->current ?: $this->decoratedResolver->resolve();
    }

    /**
     * @param \DateTime $datetime
     */
    public function setCurrent(\DateTime $datetime)
    {
        $this->current = $datetime;
    }
}
