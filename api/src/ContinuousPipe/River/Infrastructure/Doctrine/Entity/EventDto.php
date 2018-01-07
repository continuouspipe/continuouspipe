<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_event_dto_by_tide", columns={"tide_uuid"}),
 *     @ORM\Index(name="idx_event_dto_by_tide_and_event_class", columns={"tide_uuid", "event_class"})
 * })
 */
class EventDto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    public $tideUuid;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    public $eventClass;

    /**
     * @ORM\Column(type="text", nullable=false)
     *
     * @var string
     */
    public $serializedEvent;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    public $eventDatetime;
}
