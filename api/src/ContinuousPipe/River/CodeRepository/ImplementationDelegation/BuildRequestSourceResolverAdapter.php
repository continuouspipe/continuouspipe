<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\Builder\BuildRequestSourceResolver;
use ContinuousPipe\River\CodeReference;

interface BuildRequestSourceResolverAdapter extends BuildRequestSourceResolver
{
    public function supports(CodeReference $codeReference) : bool;
}
