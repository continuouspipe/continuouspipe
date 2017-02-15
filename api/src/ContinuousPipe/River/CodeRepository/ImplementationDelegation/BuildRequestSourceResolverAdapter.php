<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\Builder\BuildRequestSourceResolver;
use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

interface BuildRequestSourceResolverAdapter extends BuildRequestSourceResolver
{
    public function supports(UuidInterface $flowUuid, CodeReference $codeReference) : bool;
}
