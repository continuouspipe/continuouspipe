<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Repository
{
    const SCM_GIT = 'git';
    const SCM_MERCURIAL = 'hg';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("full_name")
     *
     * @var string
     */
    private $fullName;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $scm;

    /**
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_private")
     *
     * @var bool
     */
    private $private;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Project")
     *
     * @var Project
     */
    private $project;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Actor")
     *
     * @var Actor
     */
    private $owner;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Links")
     *
     * @var Links
     */
    private $links;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getScm(): string
    {
        return $this->scm;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @return Actor
     */
    public function getOwner(): Actor
    {
        return $this->owner;
    }

    /**
     * @return Links
     */
    public function getLinks(): Links
    {
        return $this->links;
    }
}
