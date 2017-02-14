<?php

namespace ContinuousPipe\Events\EventStore\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_event_dto_by_stream", columns={"stream"}),
 * })
 */
class EventDto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $stream;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $class;

    /**
     * @ORM\Column(type="text", nullable=false)
     *
     * @var string
     */
    private $jsonSerialized;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTimeInterface
     */
    private $creationDate;

    public function __construct(UuidInterface $uuid, string $stream, string $class, string $jsonSerialized, \DateTimeInterface $creationDate)
    {
        $this->uuid = $uuid;
        $this->stream = $stream;
        $this->class = $class;
        $this->jsonSerialized = $jsonSerialized;
        $this->creationDate = $creationDate;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getStream(): string
    {
        return $this->stream;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getJsonSerialized(): string
    {
        return $this->jsonSerialized;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }
}
