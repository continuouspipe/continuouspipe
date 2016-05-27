<?php

namespace ContinuousPipe\River\Tests\View;

use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideList;

class InMemoryTideList implements TideList
{
    /**
     * @var array|\ContinuousPipe\River\View\Tide[]
     */
    private $tides;

    /**
     * @param Tide[] $tides
     */
    public function __construct(array $tides = [])
    {
        $this->tides = $tides;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->tides;
    }
}