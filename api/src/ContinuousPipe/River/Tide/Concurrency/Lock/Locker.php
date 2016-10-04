<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

interface Locker
{
    public function lock($name, callable $callable);
}
