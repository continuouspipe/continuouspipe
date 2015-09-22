<?php

namespace ContinuousPipe\River;

interface Context
{
    /**
     * Returns true if the given key exists in the context.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @throws ContextKeyNotFound
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);

    /**
     * @return array
     */
    public function getBag();
}
