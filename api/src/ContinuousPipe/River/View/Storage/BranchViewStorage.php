<?php

namespace ContinuousPipe\River\View\Storage;

use ContinuousPipe\River\View\Tide;
use Ramsey\Uuid\UuidInterface;

interface BranchViewStorage
{
    public function save(UuidInterface $flowUUid);

    public function updateTide(Tide $tide);
}
