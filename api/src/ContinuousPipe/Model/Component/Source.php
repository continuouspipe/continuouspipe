<?php

namespace ContinuousPipe\Model\Component;

class Source
{
    /**
     * @var string
     */
    private $image;

    /**
     * @var null|string
     */
    private $tag;

    /**
     * @var null|string
     */
    private $repository;

    /**
     * @param string      $image
     * @param string|null $tag
     * @param string|null $repository
     */
    public function __construct($image, $tag = null, $repository = null)
    {
        $this->image = $image;
        $this->tag = $tag;
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return null|string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return null|string
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
