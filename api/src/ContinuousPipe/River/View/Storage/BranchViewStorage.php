<?php

namespace ContinuousPipe\River\View\Storage;

use ContinuousPipe\River\View\Tide;
use Ramsey\Uuid\UuidInterface;

interface BranchViewStorage
{
    public function save(UuidInterface $flowUuid);

    public function updateTide(Tide $tide);

    public function branchPinned(UuidInterface $flowUuid, string $branch);
    
    public function branchUnpinned(UuidInterface $flowUuid, string $branch);
}
