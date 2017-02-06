<?php

namespace ContinuousPipe\Builder\Request;

use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Logging;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Repository;
use Ramsey\Uuid\Uuid;

class BuildRequestStep
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
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return Context
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
        return $this->environment ?: [];
    }

    public function withImage(Image $image) : self
    {
        $step = clone $this;
        $step->image = $image;

        return $step;
    }

    public function withContext(Context $context) : self
    {
        $step = clone $this;
        $step->context = $context;

        return $step;
    }

    public function withEnvironment(array $environment) : self
    {
        $step = clone $this;
        $step->environment = $environment;

        return $step;
    }

    public function withSource($archiveOrRepository) : self
    {
        $step = clone $this;

        if ($archiveOrRepository instanceof Archive) {
            $step->archive = $archiveOrRepository;
        } elseif ($archiveOrRepository instanceof Repository) {
            $step->repository = $archiveOrRepository;
        } else {
            throw new \InvalidArgumentException('The argument should be an archive or a repository.');
        }

        return $step;
    }
}
