<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

use malkusch\lock\mutex\FlockMutex;

class FlockMutexLocker implements Locker
{
    /**
     * {@inheritdoc}
     */
    public function lock($name, callable $callable)
    {
        $mutex = new FlockMutex(fopen(__FILE__, 'r'));

        return $mutex->synchronized($callable);
    }
}
