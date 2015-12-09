<?php

namespace ContinuousPipe\River\Tide\Configuration;

class ArrayObject implements \ArrayAccess
{
    /**
     * @var array
     */
    private $array;

    /**
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @param string $key
     *
     * @return object
     */
    public function __get($key)
    {
        if (!$this->offsetExists($key)) {
            throw new \InvalidArgumentException(sprintf('The key "%s" do not exists', $key));
        }

        return $this->offsetGet($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return $this->array;
    }
}
