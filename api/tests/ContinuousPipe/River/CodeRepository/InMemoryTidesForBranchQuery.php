<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;

class InMemoryTidesForBranchQuery implements TidesForBranchQuery
{
    private $tides = [];

    /**
     * @return Tide[]
     */
    public function findLatestTides(Branch $branch): array
    {
        return isset($this->tides[(string) $branch]) ? $this->tides[(string) $branch] : [];
    }

    public function addTide(Branch $branch, Tide $tide)
    {
        $this->tides[(string) $branch] = isset($this->tides[(string) $branch]) ? array_merge($this->tides[(string) $branch], [$tide]) : [$tide];;
    }

}