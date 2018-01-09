<?php

namespace ContinuousPipe\Platform\FeatureFlag;

class ArrayFlagResolver implements FlagResolver
{
    private $flags;

    public function __construct(array $flags)
    {
        $this->flags = $flags;
    }

    public function isEnabled(string $flag): bool
    {
        if (!isset($this->flags[$flag])) {
            return false;
        }

        $value = $this->flags[$flag];

        return $value === true || $value === 'true';
    }

    public function overrideFlag(string $flag, $value)
    {
        $this->flags[$flag] = $value;
    }
}
