<?php

namespace Builder\Request;

use Builder\Image;
use Builder\Repository;

class BuildRequest
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Image
     */
    private $image;

    /**
     * @param Repository $repository
     * @param Image $image
     */
    public function __construct(Repository $repository, Image $image)
    {
        $this->repository = $repository;
        $this->image = $image;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }
}
