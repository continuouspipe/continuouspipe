<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class PullRequest
{
    const STATE_MERGED = 'MERGED';
    const STATE_SUPERSEDED = 'SUPERSEDED';
    const STATE_OPEN = 'OPEN';
    const STATE_DECLINED = 'DECLINED';

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $state;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uO'>")
     *
     * @var \DateTimeInterface
     */
    private $createdOn;

    /**
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uO'>")
     *
     * @var \DateTimeInterface
     */
    private $updatedOn;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\PullRequestReference")
     *
     * @var PullRequestReference
     */
    private $destination;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\PullRequestReference")
     *
     * @var PullRequestReference
     */
    private $source;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Actor")
     *
     * @var Actor
     */
    private $author;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return PullRequestReference
     */
    public function getDestination(): PullRequestReference
    {
        return $this->destination;
    }

    /**
     * @return PullRequestReference
     */
    public function getSource(): PullRequestReference
    {
        return $this->source;
    }

    /**
     * @return Actor
     */
    public function getAuthor(): Actor
    {
        return $this->author;
    }
}
