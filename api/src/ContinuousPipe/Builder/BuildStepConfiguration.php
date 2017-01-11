<?php

namespace ContinuousPipe\Builder;

class BuildStepConfiguration
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Archive
     */
    private $archive;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $environment;

    /**
     * @return Repository|null
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return Archive|null
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return Context|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
