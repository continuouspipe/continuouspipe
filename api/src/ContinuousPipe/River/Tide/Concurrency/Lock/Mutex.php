<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

use malkusch\lock\mutex\PredisMutex;

class Mutex extends PredisMutex
{
    protected function getRedisIdentifier($client)
    {
        return 'redis://[...]';
    }
}
