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
     * Re-use the existing built image.
     *
     * @var bool|null
     */
    private $reuse;

    /**
     * @param string $name
     * @param string $tag
     * @param bool $reuse
     */
    public function __construct($name, $tag, bool $reuse = null)
    {
        $this->name = $name;
        $this->tag = $tag;
        $this->reuse = $reuse;
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
     * @return bool|null
     */
    public function shouldReuse()
    {
        return $this->reuse;
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
