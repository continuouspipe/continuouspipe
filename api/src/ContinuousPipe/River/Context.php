<?php

namespace ContinuousPipe\River;

interface Context
{
    public function has($key);

    public function get($key);

    public function set($key, $value);
}
