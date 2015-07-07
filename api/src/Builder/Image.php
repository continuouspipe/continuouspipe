<?php

namespace Builder;

class Image
{
    private $name;
    private $tag;

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
