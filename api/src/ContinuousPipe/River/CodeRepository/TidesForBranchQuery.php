<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;

interface TidesForBranchQuery
{
    /**
     * @return Tide[]
     */
    public function findLatestTides(Branch $branch): array;
}