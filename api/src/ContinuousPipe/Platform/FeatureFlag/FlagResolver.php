<?php

namespace ContinuousPipe\Platform\FeatureFlag;

interface FlagResolver
{
    public function isEnabled(string $flag) : bool;
}
