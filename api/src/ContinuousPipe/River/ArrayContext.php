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

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return array_key_exists($key, $this->bag);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \RuntimeException(sprintf(
                'No key named "%s" in the context',
                $key
            ));
        }

        return $this->bag[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->bag[$key] = $value;
    }

    /**
     * @return array
     */
    public function getBag()
    {
        return $this->bag;
    }
}
