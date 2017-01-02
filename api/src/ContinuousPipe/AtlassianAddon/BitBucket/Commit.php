<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class Commit
{
    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $hash;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $message;

    /**
     * @JMS\Type("array<ContinuousPipe\AtlassianAddon\BitBucket\Commit>")
     *
     * @var Commit[]
     */
    private $parents;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\CommitAuthor")
     *
     * @var CommitAuthor
     */
    private $author;

    /**
     * @return \DateTimeInterface
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return Commit[]
     */
    public function getParents(): array
    {
        return $this->parents;
    }

    /**
     * @return CommitAuthor
     */
    public function getAuthor(): CommitAuthor
    {
        return $this->author;
    }
}
