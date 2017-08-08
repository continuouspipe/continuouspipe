<?php

namespace ContinuousPipe\Builder;

class Image
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $tag;

    /**
     * @param string $name
     * @param string $tag
     */
    public function __construct($name, $tag)
    {
        $this->name = $name;
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getTwoPartName() : string
    {
        $parts = explode('/', $this->getName());
        if (count($parts) == 2) {
            return $this->getName();
        }
        return $parts[1] . '/' . $parts[2];
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    public function withTag(string $tag)
    {
        return new self($this->name, $tag);
    }
}
