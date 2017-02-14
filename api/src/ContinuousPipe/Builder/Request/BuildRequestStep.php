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
     * @var Image|null
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
     * @var Artifact[]
     */
    private $readArtifacts = [];

    /**
     * @var Artifact[]
     */
    private $writeArtifacts = [];

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
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Artifact[]
     */
    public function getReadArtifacts(): array
    {
        return $this->readArtifacts;
    }

    /**
     * @return Artifact[]
     */
    public function getWriteArtifacts(): array
    {
        return $this->writeArtifacts;
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

    public function withReadArtifacts(array $artifacts) : self
    {
        $step = clone $this;
        $step->readArtifacts = $artifacts;

        return $step;
    }

    public function withWriteArtifacts(array $artifacts) : self
    {
        $step = clone $this;
        $step->writeArtifacts = $artifacts;

        return $step;
    }
}
