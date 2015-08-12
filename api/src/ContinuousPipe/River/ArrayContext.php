<?php

namespace ContinuousPipe\River;

class ArrayContext implements Context
{
    private $bag = [];

    /**
     * @param array $raw
     *
     * @return ArrayContext
     */
    public static function fromRaw(array $raw)
    {
        $context = new self();
        $context->bag = $raw;

        return $context;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->bag);
    }

    public function get($key)
    {
        return $this->bag[$key];
    }

    public function set($key, $value)
    {
        $this->bag[$key] = $value;
    }
}
