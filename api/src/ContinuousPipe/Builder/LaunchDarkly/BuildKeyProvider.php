<?php

namespace ContinuousPipe\Builder\LaunchDarkly;

use Inviqa\LaunchDarklyBundle\User\KeyProvider;

class BuildKeyProvider implements KeyProvider
{

    public function userKey()
    {
        return 'builder';
    }
}