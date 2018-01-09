<?php

namespace Common;

use Behat\Behat\Context\Context;
use ContinuousPipe\Platform\FeatureFlag\ArrayFlagResolver;

class FeatureFlag implements Context
{
    private $arrayFlagResolver;

    public function __construct(ArrayFlagResolver $arrayFlagResolver)
    {
        $this->arrayFlagResolver = $arrayFlagResolver;
    }

    /**
     * @Given the feature flag :flag is disabled
     */
    public function theFeatureFlagIsDisabled($flag)
    {
        $this->arrayFlagResolver->overrideFlag($flag, false);
    }
}
