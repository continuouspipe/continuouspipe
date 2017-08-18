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

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return bool|null
     */
    public function getReuse()
    {
        return $this->reuse;
    }
}
