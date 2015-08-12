<?php

namespace ContinuousPipe\River;

class ContextTree implements Context
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Context
     */
    private $parent;

    /**
     * @param Context $context
     * @param Context $parent
     */
    public function __construct(Context $context, Context $parent = null)
    {
        $this->context = $context;
        $this->parent = $parent;
    }

    public function get($key)
    {
        if ($this->context->has($key)) {
            return $this->context->get($key);
        }

        return $this->parent->get($key);
    }

    public function set($key, $value)
    {
        $this->context->set($key, $value);
    }

    public function has($key)
    {
        return $this->context->has($key) || $this->parent->has($key);
    }
}
