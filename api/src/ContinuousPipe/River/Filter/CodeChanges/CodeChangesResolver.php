<?php

namespace ContinuousPipe\River\Filter\CodeChanges;

use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

interface CodeChangesResolver
{
    public function hasChangesInFiles(UuidInterface $flowUuid, CodeReference $codeReference, array $fileGlobs) : bool;
}
