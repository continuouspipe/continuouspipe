<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

interface Locker
{
    /**
     * @param string   $name
     * @param callable $callable
     *
     * @return mixed
     */
    public function lock($name, callable $callable);
}
