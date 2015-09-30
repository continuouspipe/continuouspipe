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

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }
}
