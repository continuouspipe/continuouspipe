<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
}
