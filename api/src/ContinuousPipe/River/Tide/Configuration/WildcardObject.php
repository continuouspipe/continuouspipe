<?php

namespace ContinuousPipe\River\Tide\Configuration;

class WildcardObject extends ArrayObject
{
    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        return new ArrayObject($this->asArray());
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
    }
}
